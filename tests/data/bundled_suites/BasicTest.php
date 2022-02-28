<?php

class BasicTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected UnitTester $tester;

    // tests
    public function testMe()
    {
        $this->tester->assertTrue(true);
        $this->tester->comment('I am executed');
    }
}
