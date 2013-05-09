<?php
namespace Codeception\Module;

// here you can define custom functions for OrderGuy 

use Codeception\Codecept;
use Codeception\Event\Suite;
use Codeception\Event\Test;
use Codeception\TestCase\Cept;

class OrderHelper extends \Codeception\Module
{

    public function _initialize()
    {
        @unlink(\Codeception\Configuration::logDir().'order.txt');
        self::appendToFile('I');
    }

    public function _before(\Codeception\TestCase $test)
    {
        self::appendToFile('[');
    }

    public function _after(\Codeception\TestCase $test)
    {
        self::appendToFile(']');
    }

    public function _failed(\Codeception\TestCase $test, $fail)
    {
        self::appendToFile('F');
    }

    public function fail()
    {
        parent::fail("intentionally");
    }

    public function _beforeSuite($settings = array())
    {
        self::appendToFile('(');
    }

    public function _afterSuite()
    {
        self::appendToFile(')');
    }

    public function writeToFile($text)
    {
        self::appendToFile($text);
    }

    public static function appendToFile($marker)
    {
        $fh = fopen(\Codeception\Configuration::logDir().'order.txt', 'a');
        fwrite($fh, $marker);
    }
    
}
