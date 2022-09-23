<?php

use Codeception\Attribute\Depends;
use Codeception\Attribute\Group;
use Codeception\Test\Unit;

final class ResultIsPassedToDependentTest extends Unit
{
    /**
     * @group depends
     */
    public function testOne(): string
    {
        $this->assertTrue(true);
        return 'foo';
    }

    /**
     * @group depends
     */
    public function testTwo(): string
    {
        $this->assertTrue(true);
        return 'bar';
    }

    /**
     * @group depends
     * @depends testTwo
     * @depends testOne
     */
    public function testThree($param1, $param2)
    {
        $this->assertSame('bar', $param1, 'Unexpect value of the first parameter');
        $this->assertSame('foo', $param2, 'Unexpect value of the second parameter');
    }
}
