<?php
namespace Codeception\Lib\Interfaces;

interface IM {

    /**
     * Connect to the IM service.
     *
     * @param array $config
     *
     * @return
     */
    public function setup($config);

    /**
     * Send message to IM service.
     *
     * @param $message
     * @param array $options
     *
     * @return mixed
     */
    public function sendMessage($message, array $options = array());

    /**
     * Grab the last message on the service.
     *
     * @return mixed
     */
    public function grabLastMessage();

    /**
     * Grab the from field for the last message.
     *
     * @return mixed
     */
    public function grabLastMessageFrom();

    /**
     * Grab the message field for the last message.
     *
     * @return mixed
     */
    public function grabLastMessageContent();

    /**
     * Grab the color field for the last message.
     *
     * @return mixed
     */
    public function grabLastMessageColor();

    /**
     * Grab the date field for the last message.
     *
     * @return mixed
     */
    public function grabLastMessageDate();

    /**
     * Configuration options
     *
     * @return array
     */
    public function getRequiredConfig();

    /**
     * Default configuration options
     *
     * @return array
     */
    public function getDefaultConfig();

}