<?php
namespace Codeception\Module;

/**
 *
 * Works with Queue servers.
 *
 * Testing with a selection of remote/local queueing services, including Amazon's SQS service
 * Iron.io service and beanstalkd service.
 *
 * Supported and tested queue types are:
 *
 * * [Iron.io](http://iron.io/)
 * * [Beanstalkd](http://kr.github.io/beanstalkd/)
 * * [Amazon SQS](http://aws.amazon.com/sqs/)
 *
 * The following dependencies are needed for the listed queue servers:
 *
 * * Beanstalkd: pda/pheanstalk ~2.0
 * * Amazon SQS: aws/aws-sdk-php
 * * IronMQ: iron-io/iron_mq
 *
 * ## Status
 *
 * * Maintainer: **nathanmac**
 * * Stability:
 *     - Iron.io:    **stable**
 *     - Beanstalkd: **stable**
 *     - Amazon SQS: **stable**
 * * Contact: nathan.macnamara@outlook.com
 *
 * ## Config
 *
 * The configuration settings depending on which queueing service is being used, all the options are listed
 * here. Refer to the configuration examples below to identify the configuration options required for your chosen
 * service.
 *
 * * type - type of queueing server (defaults to beanstalkd).
 * * host - hostname/ip address of the queue server or the host for the iron.io when using iron.io service.
 * * port: 11300 - port number for the queue server.
 * * timeout: 90 - timeout settings for connecting the queue server.
 * * token - Iron.io access token.
 * * project - Iron.io project ID.
 * * key - AWS access key ID.
 * * secret - AWS secret access key.
 * * region - A region parameter is also required for AWS, refer to the AWS documentation for possible values list.
 *
 * ### Example
 * #### Example (beanstalkd)
 *
 *     modules:
 *        enabled: [Queue]
 *        config:
 *           Queue:
 *              type: 'beanstalkd'
 *              host: '127.0.0.1'
 *              port: 11300
 *              timeout: 120
 *
 * #### Example (Iron.io)
 *
 *     modules:
 *        enabled: [Queue]
 *        config:
 *           Queue:
 *              'type' => 'iron',
 *              'host' => 'mq-aws-us-east-1.iron.io',
 *              'token' => 'your-token',
 *              'project' => 'your-project-id'
 *
 * #### Example (AWS SQS)
 *
 *     modules:
 *        enabled: [Queue]
 *        config:
 *           Queue:
 *              'type' => 'aws',
 *              'key' => 'your-public-key',
 *              'secret' => 'your-secret-key',
 *              'region' => 'us-west-2'
 *
 */
class Queue extends \Codeception\Module
{

    /**
     * @var
     */
    protected $queue;

    // ----------- SETUP METHODS BELOW HERE -------------------------//

    /**
     * Setup connection and open/setup the connection with config settings
     *
     * @param \Codeception\TestCase $test
     */
    public function _before(\Codeception\TestCase $test)
    {
        $this->_openConnection();
    }

    /**
     * Provide and override for the config settings and allow custom settings depending on the service being used.
     */
    protected function validateConfig()
    {
        // Customisable requirement fields depending on the queue type selected. (aws_sqs, iron_mq, beanstalkq)
        switch (strtolower($this->config['type']))
        {
            case 'aws':
            case 'sqs':
            case 'aws_sqs':
                $this->requiredFields = array('key', 'secret', 'region');
                break;
            case 'iron':
            case 'iron_mq':
                $this->requiredFields = array('host', 'token', 'project');
                break;
            default:
                $this->requiredFields = array('host');
                $this->config = array('port' => 11300, 'timeout' => 90);
        }
        parent::validateConfig();
    }

    // ----------- SEARCH METHODS BELOW HERE ------------------------//

    /**
     * Check if a queue/tube exists on the queueing server.
     *
     * ```php
     * <?php
     *     $I->seeQueueExists('default');
     * ?>
     * ```
     *
     * @param string $queue Queue Name
     */
    public function seeQueueExists($queue)
    {
        $this->debug("see queue: [{$queue}]");
        \PHPUnit_Framework_Assert::assertContains($queue, $this->_getQueues());
    }

    /**
     * Check if a queue/tube does NOT exist on the queueing server.
     *
     * ```php
     * <?php
     *     $I->dontSeeQueueExists('default');
     * ?>
     * ```
     *
     * @param string $queue Queue Name
     */
    public function dontSeeQueueExists($queue)
    {
        $this->debug("don't see queue: [{$queue}]");
        \PHPUnit_Framework_Assert::assertNotContains($queue, $this->_getQueues());
    }

    /**
     * Check if a queue/tube is empty of all messages
     *
     * ```php
     * <?php
     *     $I->seeEmptyQueue('default');
     * ?>
     * ```
     *
     * @param string $queue Queue Name
     */
    public function seeEmptyQueue($queue)
    {
        $count = $this->_getMessagesCurrentCountOnQueue($queue);
        $this->debug("see empty queue: queue [{$queue}] has [{$count}] messages");
        \PHPUnit_Framework_Assert::assertEquals(0, $count);
    }

    /**
     * Check if a queue/tube is NOT empty of all messages
     *
     * ```php
     * <?php
     *     $I->dontSeeEmptyQueue('default');
     * ?>
     * ```
     *
     * @param string $queue Queue Name
     */
    public function dontSeeEmptyQueue($queue)
    {
        $count = $this->_getMessagesCurrentCountOnQueue($queue);
        $this->debug("don't see empty queue: queue [{$queue}] has [{$count}] messages");
        \PHPUnit_Framework_Assert::assertNotEquals(0, $count);
    }

    /**
     * Check if a queue/tube has a given current number of messages
     *
     * ```php
     * <?php
     *     $I->seeQueueHasCurrentCount('default', 10);
     * ?>
     * ```
     *
     * @param string $queue    Queue Name
     * @param int    $expected Number of messages expected
     */
    public function seeQueueHasCurrentCount($queue, $expected)
    {
        $count = $this->_getMessagesCurrentCountOnQueue($queue);
        $this->debug("see queue has current count: queue [{$queue}] has [{$count}] messages");
        \PHPUnit_Framework_Assert::assertEquals($expected, $count);
    }

    /**
     * Check if a queue/tube does NOT have a given current number of messages
     *
     * ```php
     * <?php
     *     $I->dontSeeQueueHasCurrentCount('default', 10);
     * ?>
     * ```
     *
     * @param string $queue    Queue Name
     * @param int    $expected Number of messages expected
     */
    public function dontSeeQueueHasCurrentCount($queue, $expected)
    {
        $count = $this->_getMessagesCurrentCountOnQueue($queue);
        $this->debug("don't see queue has current count: queue [{$queue}] has [{$count}] messages");
        \PHPUnit_Framework_Assert::assertNotEquals($expected, $count);
    }

    /**
     * Check if a queue/tube has a given total number of messages
     *
     * ```php
     * <?php
     *     $I->seeQueueHasTotalCount('default', 10);
     * ?>
     * ```
     *
     * @param string $queue    Queue Name
     * @param int    $expected Number of messages expected
     */
    public function seeQueueHasTotalCount($queue, $expected)
    {
        $count = $this->_getMessagesTotalCountOnQueue($queue);
        $this->debug("see queue has total count: queue [{$queue}] has [{$count}] messages");
        \PHPUnit_Framework_Assert::assertEquals($expected, $count);
    }

    /**
     * Check if a queue/tube does NOT have a given total number of messages
     *
     * ```php
     * <?php
     *     $I->dontSeeQueueHasTotalCount('default', 10);
     * ?>
     * ```
     *
     * @param string $queue    Queue Name
     * @param int    $expected Number of messages expected
     */
    public function dontSeeQueueHasTotalCount($queue, $expected)
    {
        $count = $this->_getMessagesTotalCountOnQueue($queue);
        $this->debug("don't see queue has total count: queue [{$queue}] has [{$count}] messages");
        \PHPUnit_Framework_Assert::assertNotEquals($expected, $count);
    }

    // ----------- UTILITY METHODS BELOW HERE -------------------------//

    /**
     * Add a message to a queue/tube
     *
     * ```php
     * <?php
     *     $I->addMessageToQueue('this is a messages', 'default');
     * ?>
     * ```
     *
     * @param string $message Message Body
     * @param string $queue   Queue Name
     */
    public function addMessageToQueue($message, $queue)
    {
        $this->_addMessageToQueue($message, $queue);
        $this->debug('message queued: ['. $queue . ']');
    }

    /**
     * Clear all messages of the queue/tube
     *
     * ```php
     * <?php
     *     $I->clearQueue('default');
     * ?>
     * ```
     *
     * @param string $queue Queue Name
     */
    public function clearQueue($queue)
    {
        $this->debug('clear queue: [' . $queue . ']');
        $this->_clearQueue($queue);
    }

    // ----------- GRABBER METHODS BELOW HERE -----------------------//

    /**
     * Grabber method to get the list of queues/tubes on the server
     *
     * ```php
     * <?php
     *     $I->grabQueues();
     * ?>
     * ```
     *
     * @return array List of Queues/Tubes
     */
    public function grabQueues()
    {
        $queues = $this->_getQueues();
        $this->debug('grab queues:');
        foreach ($queues as $queue) {
            $this->debug('  - [' . $queue . ']');
        }
        return $queues;
    }

    /**
     * Grabber method to get the current number of messages on the queue/tube (pending/ready)
     *
     * ```php
     * <?php
     *     $I->grabQueueCurrentCount('default');
     * ?>
     * ```
     * @param string $queue Queue Name
     *
     * @return int Count
     */
    public function grabQueueCurrentCount($queue)
    {
        return $this->_getMessagesCurrentCountOnQueue($queue);
    }

    /**
     * Grabber method to get the total number of messages on the queue/tube
     *
     * ```php
     * <?php
     *     $I->grabQueueTotalCount('default');
     * ?>
     * ```
     *
     * @param $queue Queue Name
     *
     * @return int Count
     */
    public function grabQueueTotalCount($queue)
    {
        return $this->_getMessagesTotalCountOnQueue($queue);
    }

    // ----------- CONNECTION METHODS BELOW HERE -------------//

    /**
     * Connect to the queueing server. (AWS, Iron.io and Beanstalkd)
     */
    private function _openConnection()
    {
        $this->debug('');
        switch(strtolower($this->config['type']))
        {
            case 'aws':
            case 'sqs':
            case 'aws_sqs':
                $this->queue = \Aws\Sqs\SqsClient::factory(array(
                    'credentials' => new \Aws\Common\Credentials\Credentials($this->config['key'], $this->config['secret']),
                    'region' => $this->config['region']
                )) OR \PHPUnit_Framework_Assert::fail('connection failed or timed-out.');
            break;
            case 'iron':
            case 'iron_mq':
                $this->queue = new \IronMQ(array(
                    "token" => $this->config['token'],
                    "project_id" => $this->config['project'],
                    "host" => $this->config['host']
                )) OR \PHPUnit_Framework_Assert::fail('connection failed or timed-out.');
                break;
            default:
                $this->queue = new \Pheanstalk_Pheanstalk($this->config['host'], $this->config['port'], $this->config['timeout'])
                                    OR \PHPUnit_Framework_Assert::fail('connection failed or timed-out.');
        }
    }

    /**
     * Post/Put a message on to the queue server
     *
     * @param string $message Message Body to be send
     * @param string $queue   Queue Name
     */
    private function _addMessageToQueue($message, $queue)
    {
        switch(strtolower($this->config['type']))
        {
            case 'aws':
            case 'sqs':
            case 'aws_sqs':
                $this->queue->sendMessage(array(
                    'QueueUrl' => $this->_getQueueURL($queue),
                    'MessageBody' => $message,
                ));
                break;
            case 'iron':
            case 'iron_mq':
                $this->queue->postMessage($queue, $message);
                break;
            default:
                $this->queue->putInTube($queue, $message);
        }
    }

    /**
     * Return a list of queues/tubes on the queueing server
     *
     * @return array Array of Queues
     */
    private function _getQueues()
    {
        switch(strtolower($this->config['type']))
        {
            case 'aws':
            case 'sqs':
            case 'aws_sqs':
                $queueNames = array();
                $queues = $this->queue->listQueues(array('QueueNamePrefix' => ''))->get('QueueUrls');
                foreach ($queues as $queue) {
                    $tokens = explode('/', $queue);
                    $queueNames[] = $tokens[sizeof($tokens)-1];
                }
                return $queueNames;
            case 'iron':
            case 'iron_mq':
                // Format the output to suit
                $queues = array();
                foreach($this->queue->getQueues() as $queue) {
                    $queues[] = $queue->name;
                }
                return $queues;
            default:
                return $this->queue->listTubes();
        }
    }

    /**
     * Count the current number of messages on the queue.
     *
     * @param $queue Queue Name
     *
     * @return int Count
     */
    private function _getMessagesCurrentCountOnQueue($queue)
    {
        switch(strtolower($this->config['type']))
        {
            case 'aws':
            case 'sqs':
            case 'aws_sqs':
                return $this->queue->getQueueAttributes(array(
                    'QueueUrl' => $this->_getQueueURL($queue),
                    'AttributeNames' => array('ApproximateNumberOfMessages'),
                ))->get('Attributes')['ApproximateNumberOfMessages'];
                break;
            case 'iron':
            case 'iron_mq':
                try {
                    return $this->queue->getQueue('my_queue')->size;
                } catch (\Http_Exception $ex) {
                    $this->debug('queue: [' . $queue . '] not found');
                    \PHPUnit_Framework_Assert::fail('queue [' . $queue . '] not found');
                }
            default:
                try {
                    return $this->queue->statsTube($queue)['current-jobs-ready'];
                } catch (\Pheanstalk_Exception_ServerException $ex) {
                    $this->debug('queue: [' . $queue . '] not found');
                    \PHPUnit_Framework_Assert::fail('queue [' . $queue . '] not found');
                }
        }
    }

    /**
     * Count the total number of messages on the queue.
     *
     * @param $queue Queue Name
     *
     * @return int Count
     */
    private function _getMessagesTotalCountOnQueue($queue)
    {
        switch(strtolower($this->config['type']))
        {
            case 'aws':
            case 'sqs':
            case 'aws_sqs':
                return $this->queue->getQueueAttributes(array(
                    'QueueUrl' => $this->_getQueueURL($queue),
                    'AttributeNames' => array('ApproximateNumberOfMessages'),
                ))->get('Attributes')['ApproximateNumberOfMessages'];
                break;
            case 'iron':
            case 'iron_mq':
                try {
                    return $this->queue->getQueue($queue)->total_messages;
                } catch (\Http_Exception $ex) {
                    $this->debug('queue: [' . $queue . '] not found');
                    \PHPUnit_Framework_Assert::fail('queue [' . $queue . '] not found');
                }
            default:
                try {
                    return $this->queue->statsTube($queue)['total-jobs'];
                } catch (\Pheanstalk_Exception_ServerException $ex) {
                    $this->debug('queue: [' . $queue . '] not found');
                    \PHPUnit_Framework_Assert::fail('queue [' . $queue . '] not found');
                }
        }
    }

    /**
     * Clear the queue/tube of all messages
     *
     * @param $queue Queue Name
     */
    private function _clearQueue($queue)
    {
        switch(strtolower($this->config['type']))
        {
            case 'aws':
            case 'sqs':
            case 'aws_sqs':
                $queueURL = $this->_getQueueURL($queue);
                while(true) {
                    $res = $this->queue->receiveMessage(array('QueueUrl' => $queueURL));

                    if ($res->getPath('Messages')) {
                        foreach ($res->getPath('Messages') as $msg) {
                            $this->debug("  - delete message: ".$msg['MessageId']);
                        }
                        // Do something useful with $msg['Body'] here
                        $this->queue->deleteMessage(array(
                            'QueueUrl'      => $queueURL,
                            'ReceiptHandle' => $msg['ReceiptHandle']
                        ));
                    } else {
                        break;
                    }
                }
            break;
            case 'iron':
            case 'iron_mq':
                try {
                    $this->queue->clearQueue($queue);
                } catch (\Http_Exception $ex) {
                    $this->debug('queue: [' . $queue . '] not found');
                    \PHPUnit_Framework_Assert::fail('queue [' . $queue . '] not found');
                }
                break;
            default:
                while($job = $this->queue->reserve(0)) {
                    $this->queue->delete($job);
                }
        }
    }

    /**
     * Get the queue/tube URL from the queue name (AWS function only)
     *
     * @param $queue Queue Name
     *
     * @return string Queue URL
     */
    private function _getQueueURL($queue)
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
}