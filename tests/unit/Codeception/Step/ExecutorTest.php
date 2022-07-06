<?php

declare(strict_types=1);

namespace Codeception\Step;

use Codeception\Attribute\DataProvider;
use PHPUnit\Framework\TestCase;

final class ExecutorTest extends TestCase
{
    #[DataProvider('valuesProvider')]
    public function testRun(bool $returnValue)
    {
        $expected = $returnValue;

        $executor = new Executor(fn (): bool => $returnValue);
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
