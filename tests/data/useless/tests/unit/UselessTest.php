<?php

class UselessTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testMakeNoAssertions()
    {

    }

    public function testMakeUnexpectedAssertion()
    {
        if (version_compare(\PHPUnit\Runner\Version::id(), '7.2.0', '<')) {
            $this->markTestSkipped('Not supported before PHPUnit 7.2');
        }
        $this->expectNotToPerformAssertions();
        $this->assertTrue(true);
    }
}
