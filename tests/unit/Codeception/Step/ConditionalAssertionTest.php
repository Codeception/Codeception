<?php

declare(strict_types=1);

namespace Codeception\Step;

class ConditionalAssertionTest extends \PHPUnit\Framework\TestCase
{
    public function testCantSeeToString()
    {
        $assertion = new ConditionalAssertion('dontSee', ['text']);
        $this->assertSame('cant see "text"', $assertion->toString(200));
    }
}
