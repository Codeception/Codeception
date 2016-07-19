<?php
namespace Codeception\Lib\Connector\Yii2;

use yii\mail\BaseMailer;

class TestMailer
{
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