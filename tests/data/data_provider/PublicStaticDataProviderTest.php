<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class PublicStaticDataProviderTest extends TestCase
{
    /**
     * @dataProvider getData
     */
    public function testDataProvider($arg1, $arg2): void
    {
    }

    public static function getData(): array
    {
        return [
            'foo' => ['foo', 2],
            'bar' => ['bar', 3],
            'not baz' => ['baz', 5],
        ];
    }
}
