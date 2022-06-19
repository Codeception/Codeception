<?php

use Codeception\Attribute\Group;
use PHPUnit\Framework\TestCase;

#[Group('multiple-fail')]
final class MultipleFailingTest extends TestCase
{
    public function testMe()
    {
        $this->assertFalse(true);
    }

    public function testMeTwo()
    {
        $this->assertFalse(true);
    }

    public function testMeThree()
    {
        $this->assertFalse(true);
    }
}
