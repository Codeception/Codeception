<?php

use \Codeception\Step\ConditionalAssertion;

class ConditionalAssertionTest extends \PHPUnit\Framework\TestCase
{
    public function testCantSeeToString()
    {
        $assertion = new ConditionalAssertion('dontSee', ['text']);
        $this->assertEquals('cant see "text"', $assertion->toString(200));
    }
}
