<?php
namespace Codeception\Lib\Driver;

use Codeception\Lib\Interfaces\Queue;

class AmazonSQS implements Queue
{

    protected $queue;

    /**
     * Connect to the queueing server. (AWS, Iron.io and Beanstalkd)
     * @param array $config
     * @return
     */
    public function openConnection($config)
    {
        $this->queue = \Aws\Sqs\SqsClient::factory(array(
            'credentials' => new \Aws\Common\Credentials\Credentials($config['key'], $config['secret']),
            'region' => $config['region']
        )) OR \PHPUnit_Framework_Assert::fail('connection failed or timed-out.');

    }

    /**
     * Post/Put a message on to the queue server
     *
     * @param string $message Message Body to be send
     * @param string $queue Queue Name
     */
    public function addMessageToQueue($message, $queue)
    {
        $this->queue->sendMessage(array(
            'QueueUrl' => $this->getQueueURL($queue),
            'MessageBody' => $message,
        ));
    }

    /**
     * Return a list of queues/tubes on the queueing server
     *
     * @return array Array of Queues
     */
    public function getQueues()
    {
        $queueNames = array();
        $queues = $this->queue->listQueues(array('QueueNamePrefix' => ''))->get('QueueUrls');
        foreach ($queues as $queue) {
            $tokens = explode('/', $queue);
            $queueNames[] = $tokens[sizeof($tokens)-1];
        }
        return $queueNames;
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
        return $this->queue->getQueueAttributes(array(
            'QueueUrl' => $this->_getQueueURL($queue),
            'AttributeNames' => array('ApproximateNumberOfMessages'),
        ))->get('Attributes')['ApproximateNumberOfMessages'];
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
        return $this->queue->getQueueAttributes(array(
            'QueueUrl' => $this->_getQueueURL($queue),
            'AttributeNames' => array('ApproximateNumberOfMessages'),
        ))->get('Attributes')['ApproximateNumberOfMessages'];

    }

    public function clearQueue($queue)
    {
        $queueURL = $this->_getQueueURL($queue);
        while (true) {
            $res = $this->queue->receiveMessage(array('QueueUrl' => $queueURL));

            if (!$res->getPath('Messages')) {
                return;
            }
            foreach ($res->getPath('Messages') as $msg) {
                $this->debug("  - delete message: ".$msg['MessageId']);
            }
            // Do something useful with $msg['Body'] here
            $this->queue->deleteMessage(array(
                'QueueUrl'      => $queueURL,
                'ReceiptHandle' => $msg['ReceiptHandle']
            ));
        }
    }

    /**
     * Get the queue/tube URL from the queue name (AWS function only)
     *
     * @param $queue Queue Name
     *
     * @return string Queue URL
     */
    private function getQueueURL($queue)
    {
        $queues = $this->queue->listQueues(array('QueueNamePrefix' => ''))->get('QueueUrls');
        foreach ($queues as $queueURL) {
            $tokens = explode('/', $queueURL);
            if (strtolower($queue) == strtolower($tokens[sizeof($tokens)-1]))
                return $queueURL;
        }
        $this->debug('queue: [' . $queue . '] not found');
        \PHPUnit_Framework_Assert::fail('queue [' . $queue . '] not found');
    }

    public function getRequiredConfig()
    {
        return ['key', 'secret', 'region'];
    }

    public function getDefaultConfig()
    {
        return [];
    }
}