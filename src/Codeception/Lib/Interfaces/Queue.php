<?php


namespace Codeception\Lib\Interfaces;


interface Queue {

    /**
     * Connect to the queueing server. (AWS, Iron.io and Beanstalkd)
     */
    public function openConnection();

    /**
     * Post/Put a message on to the queue server
     *
     * @param string $message Message Body to be send
     * @param string $queue   Queue Name
     */
    public function addMessageToQueue($message, $queue);

    /**
     * Return a list of queues/tubes on the queueing server
     *
     * @return array Array of Queues
     */
    public function getQueues();

    /**
     * Count the current number of messages on the queue.
     *
     * @param $queue Queue Name
     *
     * @return int Count
     */
    public function getMessagesCurrentCountOnQueue($queue);

    /**
     * Count the total number of messages on the queue.
     *
     * @param $queue Queue Name
     *
     * @return int Count
     */
    public function getMessagesTotalCountOnQueue($queue);

    public function clearQueue($queue);

}