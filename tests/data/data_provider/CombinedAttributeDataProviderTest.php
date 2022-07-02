<?php

declare(strict_types=1);

use Codeception\Attribute\DataProvider;
use Codeception\Attribute\Examples;
use PHPUnit\Framework\TestCase;

class CombinedAttributeDataProviderTest extends TestCase
{
    #[DataProvider('getData')]
    #[Examples('xyz1', 2)]
    #[Examples('xyz2', 3)]
    public function testCombined($arg1, $arg2): void
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
}
