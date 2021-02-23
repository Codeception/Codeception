<?php

declare(strict_types=1);

namespace Codeception\Step;

class ConditionalAssertionTest extends \PHPUnit\Framework\TestCase
{
    public function testCantSeeToString(): void
    {
        $assertion = new ConditionalAssertion('dontSee', ['text']);
        $this->assertEquals('cant see "text"', $assertion->toString(200));
    }
}
