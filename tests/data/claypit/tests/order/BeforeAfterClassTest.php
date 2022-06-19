<?php

use Codeception\Attribute\Group;
use Codeception\Module\OrderHelper as OrderHelperModule;
use Codeception\Test\Unit;

#[Group('App'), Group('New')]
final class BeforeAfterClassTest extends Unit
{
    /**
     * @beforeClass
     */
    public static function setUpSomeSharedFixtures()
    {
        OrderHelperModule::appendToFile('{');
    }

    public function testOne()
    {
        OrderHelperModule::appendToFile('1');
    }

    public function testTwo()
    {
        OrderHelperModule::appendToFile('2');
    }

    /**
     * @afterClass
     */
    public static function tearDownSomeSharedFixtures()
    {
        OrderHelperModule::appendToFile('}');
    }
}
