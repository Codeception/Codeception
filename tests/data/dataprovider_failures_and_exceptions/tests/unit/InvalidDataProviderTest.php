<?php

use Codeception\Attribute\DataProvider;
use Codeception\Test\Unit;

final class InvalidDataProviderTest extends Unit
{
    /**
     * @dataProvider dependentProvider
     */
    public function testInvalidDataProvider($a)
    {
        $this->assertTrue(true);
    }

    public function dependentProvider()
    {
        throw new Exception('Data provider failed');
    }
}
