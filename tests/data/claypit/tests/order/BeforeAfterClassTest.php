<?php

use Codeception\Attribute\AfterClass;
use Codeception\Attribute\BeforeClass;
use Codeception\Attribute\Group;
use Codeception\Module\OrderHelper as OrderHelperModule;
use Codeception\Test\Unit;

#[Group('App'), Group('New')]
final class BeforeAfterClassTest extends Unit
{
    #[BeforeClass]
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

    #[AfterClass]
    public static function tearDownSomeSharedFixtures()
    {
        OrderHelperModule::appendToFile('}');
    }
}
