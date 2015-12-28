<?php
class SomeErrorClass {


    public function some_method()
    {
        $a = [];

        $a .= 'test';

    }

}


class ErrorTest extends \Codeception\Test\Format\TestCase
{

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @group error
     */
    function testGetError()
    {

        $test = new SomeErrorClass;

        $test->some_method();

    }

}