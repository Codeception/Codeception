<?php

namespace Codeception\Module;

class MessageHelper extends \Codeception\Module
{
    protected $config = [
        'message1' => 'DEFAULT MESSAGE1.',
        'message2' => 'DEFAULT MESSAGE2.',
        'message3' => 'DEFAULT MESSAGE3.',
        'message4' => 'DEFAULT MESSAGE4.',
    ];

    public function getMessage($name)
    {
        return $this->config[$name];
    }
}
