<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Codeception\Attribute\DataProvider;

class AttributeDataProviderTest extends TestCase
{
    #[DataProvider('getData')]
    public function testDataProvider($arg1, $arg2): void
    {
    }

    #[DataProvider('getData')]
    #[DataProvider('getData2')]
    public function testDataProviderMultipleTimes($arg1, $arg2): void
    {
    }

    public function getData(): array
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
            'foo2' => ['foo2', 8],
            'bar2' => ['bar2', 9],
            'not baz2' => ['baz2', 10],
        ];
    }
}
