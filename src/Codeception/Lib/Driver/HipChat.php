<?php
namespace Codeception\Lib\Driver;

use Codeception\Lib\Interfaces\IM;

class HipChat implements IM
{

    protected $client;
    protected $roomID;

    /**
     * @var array Default Supported Colors
     */
    protected $colors = array(
        'yellow' => \GorkaLaucirica\HipchatAPIv2Client\Model\Message::COLOR_YELLOW,
        'red'    => \GorkaLaucirica\HipchatAPIv2Client\Model\Message::COLOR_RED,
        'gray'   => \GorkaLaucirica\HipchatAPIv2Client\Model\Message::COLOR_GRAY,
        'green'  => \GorkaLaucirica\HipchatAPIv2Client\Model\Message::COLOR_GREEN,
        'purple' => \GorkaLaucirica\HipchatAPIv2Client\Model\Message::COLOR_PURPLE,
        'random' => \GorkaLaucirica\HipchatAPIv2Client\Model\Message::COLOR_RANDOM
    );

    /**
     * Connect to the IM service.
     *
     * @param array $config
     */
    public function setup($config)
    {
        $auth = new \GorkaLaucirica\HipchatAPIv2Client\Auth\OAuth2($config['token']);
        $this->client = new \GorkaLaucirica\HipchatAPIv2Client\Client($auth);

        $this->roomID = $config['room'];
    }

    /**
     * Send message to IM service.
     *
     * @param $message
     * @param array $options
     *
     * @return mixed
     */
    public function sendMessage($message, array $options = array())
    {
        try {
            $envelope = new \GorkaLaucirica\HipchatAPIv2Client\Model\Message();
            $envelope->setMessage($message);
            $envelope->setNotify(isset($options['notify']) && $options['notify']);
            $envelope->setColor((isset($options['color']) && isset($this->colors[$options['color']])) ? $this->colors[$options['color']] : $this->colors['yellow']);

            $roomAPI = new \GorkaLaucirica\HipchatAPIv2Client\API\RoomAPI($this->client);
            $roomAPI->sendRoomNotification($this->roomID, $envelope);
        } catch (\Exception $ex) {
            \PHPUnit_Framework_Assert::fail('connection failed or timed-out, please check your connection and ensure your api token has sufficient permissions.');
        }
    }

    /**
     * Grab the last message on the service.
     *
     * @return mixed
     */
    public function grabLastMessage()
    {
        try {
            $roomAPI = new \GorkaLaucirica\HipchatAPIv2Client\API\RoomAPI($this->client);
            $messages = $roomAPI->getRecentHistory($this->roomID, array('max-results' => 1));

            if (count($messages) <= 0) {
                \PHPUnit_Framework_Assert::fail('the selected room/channel has no messages.');
            }
            return $messages[0]->toJson();

        } catch (\Exception $ex) {
            \PHPUnit_Framework_Assert::fail('connection failed or timed-out, please check your connection and ensure your api token has sufficient permissions.');
        }
    }

    /**
     * Grab the from field for the last message.
     *
     * @return string
     */
    public function grabLastMessageFrom()
    {
        return $this->grabLastMessage()['from'];
    }

    /**
     * Grab the message field for the last message.
     *
     * @return mixed
     */
    public function grabLastMessageContent()
    {
        return $this->grabLastMessage()['message'];
    }

    /**
     * Grab the color field for the last message.
     *
     * @return mixed
     */
    public function grabLastMessageColor()
    {
        return $this->grabLastMessage()['color'];
    }

    /**
     * Grab the date field for the last message.
     *
     * @return mixed
     */
    public function grabLastMessageDate()
    {
        return $this->grabLastMessage()['date'];
    }

    /**
     * Configuration options
     *
     * @return array
     */
    public function getRequiredConfig()
    {
        return ['token', 'room'];
    }

    /**
     * Default configuration options
     *
     * @return array
     */
    public function getDefaultConfig()
    {
        return [];
    }
}