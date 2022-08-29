<?php

use Codeception\Attribute\DataProvider;
use Codeception\Attribute\Group;
use Codeception\Module\OrderHelper as OrderHelperModule;
use Codeception\Test\Unit;

#[Group('App'), Group('New')]
final class BeforeAfterClassWithDataProviderTest extends Unit
{
    /**
     * @beforeClass
     */
    public static function setUpSomeSharedFixtures()
    {
        OrderHelperModule::appendToFile('{');
    }

    /**
     * @dataProvider getAbc
     */
    public function testAbc(string $letter)
    {
        OrderHelperModule::appendToFile($letter);
    }

    public static function getAbc(): array
    {
        return [['A'], ['B'], ['C']];
    }

    /**
     * @afterClass
     */
    public static function tearDownSomeSharedFixtures()
    {
        OrderHelperModule::appendToFile('}');
    }
}
