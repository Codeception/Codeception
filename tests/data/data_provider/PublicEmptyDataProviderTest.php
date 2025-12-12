<?php

declare(strict_types=1);

namespace data\data_provider;

use PHPUnit\Framework\TestCase;

class PublicEmptyDataProviderTest extends TestCase
{
    /**
     * @dataProvider getData
     */
    public function testDataProvider($arg1, $arg2): void
    {
    }

    public function getData(): array
    {
        return [];
    }
}
