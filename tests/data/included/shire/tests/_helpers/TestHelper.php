<?php

namespace Shire;

// here you can define custom functions for TestGuy

class TestHelper extends \Codeception\Module
{
    public function seeEquals($expected, $actual): void
    {
        \PHPUnit_Framework_Assert::assertEquals($expected, $actual);
    }
}
