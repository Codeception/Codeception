<?php

namespace Codeception\Module;

use Codeception\Configuration;
use Codeception\Module;
use Codeception\Testable;

class OrderHelper extends Module
{
    public function _initialize()
    {
        self::appendToFile('I');
    }

    public function _before(Testable $test)
    {
        self::appendToFile('[');
    }

    public function _after(Testable $test)
    {
        self::appendToFile(']');
    }

    public function _failed(Testable $test, $fail)
    {
        self::appendToFile('F');
    }

    public function failNow()
    {
        $this->fail("intentionally");
    }

    public function seeFailNow()
    {
        $this->fail("intentionally");
    }

    public function dontSeeFailNow()
    {
        $this->fail("intentionally");
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
        $fh = fopen(Configuration::outputDir().'order.txt', 'a');
        fwrite($fh, $marker);
    }
}
