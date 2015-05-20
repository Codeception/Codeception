<?php


namespace Codeception\Lib\Interfaces;


interface Queue {

    /**
     * Connect to the queueing server.
     * @param array $config
     * @return
     */
    public function openConnection($config);

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

    public function getRequiredConfig();

    public function getDefaultConfig();

}