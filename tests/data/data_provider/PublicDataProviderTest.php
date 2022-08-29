<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class PublicDataProviderTest extends TestCase
{
    /**
     * @dataProvider getData
     */
    public function testDataProvider($arg1, $arg2): void
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
