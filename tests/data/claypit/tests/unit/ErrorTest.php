<?php

class SomeErrorClass
{
    public function someMethod()
    {
        $a = [];

        $a .= 'test';
    }
}


class ErrorTest extends \Codeception\Test\Unit
{
    protected UnitTester|CodeGuy $tester;

    /**
     * @group error
     */
    public function testGetError()
    {
        $test = new SomeErrorClass();

        $test->someMethod();
    }
}
