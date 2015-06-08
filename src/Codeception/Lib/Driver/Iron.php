<?php
namespace Codeception\Lib\Driver;

use Codeception\Lib\Interfaces\Queue;

class Iron implements Queue
{
    /**
     * @var \IronMQ
     */
    protected $queue;

    /**
     * Connect to the queueing server. (AWS, Iron.io and Beanstalkd)
     * @param array $config
     * @return
     */
    public function openConnection($config)
    {
        $this->queue = new \IronMQ([
            "token"      => $config['token'],
            "project_id" => $config['project'],
            "host"       => $config['host']
        ]);
        if (!$this->queue) {
            \PHPUnit_Framework_Assert::fail('connection failed or timed-out.');
        }
    }

    /**
     * Post/Put a message on to the queue server
     *
     * @param string $message Message Body to be send
     * @param string $queue Queue Name
     */
    public function addMessageToQueue($message, $queue)
    {
        $this->queue->postMessage($queue, $message);
    }

    /**
     * Return a list of queues/tubes on the queueing server
     *
     * @return array Array of Queues
     */
    public function getQueues()
    {
        // Format the output to suit
        $queues = [];
        foreach ($this->queue->getQueues() as $queue) {
            $queues[] = $queue->name;
        }
        return $queues;
    }

    /**
     * Count the current number of messages on the queue.
     *
     * @param $queue Queue Name
     *
     * @return int Count
     */
    public function getMessagesCurrentCountOnQueue($queue)
    {
        try {
            return $this->queue->getQueue($queue)->size;
        } catch (\Http_Exception $ex) {
            \PHPUnit_Framework_Assert::fail("queue [$queue] not found");
        }
    }

    /**
     * Count the total number of messages on the queue.
     *
     * @param $queue Queue Name
     *
     * @return int Count
     */
    public function getMessagesTotalCountOnQueue($queue)
    {
        try {
            return $this->queue->getQueue($queue)->total_messages;
        } catch (\Http_Exception $e) {
            \PHPUnit_Framework_Assert::fail("queue [$queue] not found");
        }
    }

    public function clearQueue($queue)
    {
        try {
            $this->queue->clearQueue($queue);
        } catch (\Http_Exception $ex) {
            \PHPUnit_Framework_Assert::fail("queue [$queue] not found");
        }
    }

    public function getRequiredConfig()
    {
        return ['host', 'token', 'project'];
    }

    public function getDefaultConfig()
    {
        return [];
    }
}
