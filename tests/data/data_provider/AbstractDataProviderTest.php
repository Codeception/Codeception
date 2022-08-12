<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

abstract class AbstractDataProviderClass extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testDataProvider(string $thing): void
    {
        self::assertSame('foo', $thing);
    }

    abstract public function provideData(): array;
}

class AbstractDataProviderTest extends AbstractDataProviderClass
{
    public function provideData(): array
    {
        return [
            'foo' => ['foo']
        ];
    }
}
