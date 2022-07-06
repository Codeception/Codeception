<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class PrivateDataProviderTest extends TestCase
{
    /**
     * @dataProvider getData
     */
    public function testDataProvider($arg1, $arg2): void
    {
    }

    private function getData(): array
    {
        return [
            ['foo', 5],
            ['bar', 6],
            ['baz', 7],
        ];
    }
}
