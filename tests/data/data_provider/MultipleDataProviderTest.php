<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class MultipleDataProviderTest extends TestCase
{
    /**
     * @dataProvider getData1
     * @dataProvider getData2
     */
    public function testDataProvider($arg1, $arg2): void
    {
    }

    public function getData1(): array
    {
        return [
            'foo' => ['foo', 5],
            'bar' => ['bar', 6],
            'not baz' => ['baz', 7],
        ];
    }

    public function getData2(): array
    {
        return [
            'abc' => ['abc', 8],
            'def' => ['def', 9],
        ];
    }
}
