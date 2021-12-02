<?php


class failingTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected UnitTester $tester;

    // tests
    public function testFailing()
    {
        throw new \RuntimeException('in test');
    }
}
