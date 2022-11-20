<?php

class UselessTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testMakeNoAssertions(): void
    {
    }

    public function testExpectsNotToPerformAssertions(): void
    {
        $this->expectNotToPerformAssertions();
    }

    public function testMakeUnexpectedAssertion(): void
    {
        $this->expectNotToPerformAssertions();
        $this->assertTrue(true);
    }
}
