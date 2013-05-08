<?php
namespace Codeception\Module;

// here you can define custom functions for OrderGuy 

use Codeception\Codecept;
use Codeception\Event\Suite;
use Codeception\TestCase\Cept;

class OrderHelper extends \Codeception\Module
{

    public function _initialize()
    {
        @unlink(\Codeception\Configuration::logDir().'order.txt');
        self::appendToFile('I');
    }

    public function _before()
    {
        self::appendToFile('[');
    }

    public function _after()
    {
        self::appendToFile(']');
    }

    public function _failed()
    {
        self::appendToFile('F');
    }

    public function fail()
    {
        parent::fail("intentionally");
    }

    public function _beforeSuite()
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
