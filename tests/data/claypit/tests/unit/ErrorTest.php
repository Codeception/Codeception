<?php

use Codeception\Attribute\Group;
use Codeception\Test\Unit;

class SomeErrorClass
{
    public function someMethod(): void
    {
        $a = [];

        $a .= 'test';
    }
}

final class ErrorTest extends Unit
{
    protected UnitTester|CodeGuy $tester;

    #[Group('error')]
    public function testGetError()
    {
        $test = new SomeErrorClass();

        $test->someMethod();
    }
}
