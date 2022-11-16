<?php

use Codeception\Attribute\Depends;
use Codeception\Attribute\Group;
use Codeception\Test\Unit;

final class DependsTest extends Unit
{
    #[Group('depends')]
    #[Depends('testOne')]
    public function testTwo($res)
    {
        $this->assertTrue(true);
        $this->assertSame(1, $res);
    }

    #[Group('depends')]
    #[Depends('testFour')]
    public function testThree()
    {
        $this->assertTrue(true);
    }

    public function testFour()
    {
        $this->assertTrue(true);
    }

    #[Depends('testThree', 'testFour')]
    public function testFive()
    {
        $this->assertTrue(true);
    }

    #[Group('depends')]
    public function testOne(): int
    {
        $this->assertTrue(false);
        return 1;
    }
}
