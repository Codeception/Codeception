<?php

use Codeception\Test\Unit;
use PHPUnit\Framework\AssertionFailedError;

class ExpectedFailureTest extends Unit
{
    protected ScenarioGuy $tester;

    public function testExpectedFailure()
    {
        static::fail(':-(');
    }

    public function testExpectedException()
    {
        $this->expectException(AssertionFailedError::class);
        $this->tester->assertFalse(true);
    }
}
