<?php

declare(strict_types=1);

namespace Codeception\Module;

use Codeception\Module;

class MessageHelper extends Module
{
    protected array $config = [
        'message1' => 'DEFAULT MESSAGE1.',
        'message2' => 'DEFAULT MESSAGE2.',
        'message3' => 'DEFAULT MESSAGE3.',
        'message4' => 'DEFAULT MESSAGE4.',
    ];

    public function getMessage($name): string
    {
        return $this->config[$name];
    }
}
