<?php

namespace Codeception\Util;

class Fixtures
{
    protected static $fixtures = array();

    public static function add($name, $data) {
        self::$fixtures[$name] = $data;
    }

    public static function get($name)
    {
        if (!isset(self::$fixtures[$name])) throw new \RuntimeException("$name not found in fixtures");
        return self::$fixtures[$name];
    }
    
    public function __() {
        
    }

}
