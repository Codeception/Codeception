<?php
namespace Codeception\Lib\Connector\Yii2;

use yii\mail\BaseMailer;
use yii\mail\BaseMessage;

class TestMailer extends BaseMailer
{
    public $messageClass = 'yii\swiftmailer\Message';

    private $sentMessages = [];

    protected function sendMessage($message)
    {
        $this->sentMessages[] = $message;
    }

    public function getSentMessages()
    {
        return $this->sentMessages;
    }

    public function reset()
    {
        $this->sentMessages = [];
    }
}

