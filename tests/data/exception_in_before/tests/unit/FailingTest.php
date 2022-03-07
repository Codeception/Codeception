<?php

class FailingTest extends \Codeception\Test\Unit
{
    protected UnitTester $tester;

    public function testFailing()
    {
        throw new \RuntimeException('in test');
    }
}
