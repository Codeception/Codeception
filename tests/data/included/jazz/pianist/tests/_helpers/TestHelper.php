<?php
namespace Jazz\Pianist;

// here you can define custom functions for TestGuy 

class TestHelper extends \Codeception\Module
{
    public function seeEquals($expected, $actual) {
        \PHPUnit_Framework_Assert::assertEquals($expected, $actual);
    }
}
