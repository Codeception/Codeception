<?php

class BasicTest extends \Codeception\Test\Unit
{
    protected UnitTester $tester;

    public function testMe()
    {
        $this->tester->assertTrue(true);
        $this->tester->comment('I am executed');
    }
}
