<?php

namespace Codeception\PHPUnit;

class Init
{
    /**
     * @api
     */
    public static function init()
    {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'phpunit7-interfaces.php';
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'shim.php';
    }
}