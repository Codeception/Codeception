<?php
namespace Shire;

// here you can define custom functions for TestGuy 

class TestHelper extends \Codeception\Module
{
    public function seeEquals($expected, $actual) {
        \PHPUnit\Framework\Assert::assertEquals($expected, $actual);
    }
}
