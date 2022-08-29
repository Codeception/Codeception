<?php

declare(strict_types=1);

namespace Codeception\Step;

class ExecutorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider valuesProvider
     */
    public function testRun(bool $returnValue)
    {
        $expected = $returnValue;

        $executor = new \Codeception\Step\Executor(function () use ($returnValue) {
            return $returnValue;
        });
        $actual = $executor->run();

        $this->assertSame($expected, $actual);
    }

    /**
     * @return bool[][]
     */
    public function valuesProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }
}
