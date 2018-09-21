<?php
namespace Codeception\Lib\Connector\Yii2;

use yii\mail\BaseMailer;

class TestMailer extends BaseMailer
{
    public $messageClass = \yii\swiftmailer\Message::class;

    /**
     * @var \Closure
     */
    public $callback;

    protected function sendMessage($message)
    {
        call_user_func($this->callback, $message);
        return true;
    }
    
    protected function saveMessage($message)
    {
        call_user_func($this->callback, $message);
        return true;
    }
}
