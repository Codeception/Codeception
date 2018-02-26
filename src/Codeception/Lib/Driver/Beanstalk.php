<?php
namespace Codeception\Lib\Driver;

use Codeception\Lib\Interfaces\Queue;
use Pheanstalk\Pheanstalk;
use Pheanstalk\Exception\ConnectionException;

class Beanstalk implements Queue
{

    /**
     * @var Pheanstalk
     */
    protected $queue;

    public function openConnection($config)
    {
        $this->queue = new Pheanstalk($config['host'], $config['port'], $config['timeout']);
    }

    /**
     * Post/Put a message on to the queue server
     *
     * @param string $message Message Body to be send
     * @param string $queue Queue Name
     */
    public function addMessageToQueue($message, $queue)
    {
        $this->queue->putInTube($queue, $message);
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
            return $this->queue->statsTube($queue)['total-jobs'];
        } catch (ConnectionException $ex) {
            \PHPUnit\Framework\Assert::fail("queue [$queue] not found");
        }
    }

    public function clearQueue($queue = 'default')
    {
        while ($job = $this->queue->reserveFromTube($queue, 0)) {
            $this->queue->delete($job);
        }
    }

    /**
     * Return a list of queues/tubes on the queueing server
     *
     * @return array Array of Queues
     */
    public function getQueues()
    {
        return $this->queue->listTubes();
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
            return $this->queue->statsTube($queue)['current-jobs-ready'];
        } catch (ConnectionException $e) {
            \PHPUnit\Framework\Assert::fail("queue [$queue] not found");
        }
    }

    public function getRequiredConfig()
    {
        return ['host'];
    }

    public function getDefaultConfig()
    {
        return ['port' => 11300, 'timeout' => 90];
    }
}
