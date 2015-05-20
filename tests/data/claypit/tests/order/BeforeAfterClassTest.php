<?php
/**
 * @group App
 * @group New
 */
class BeforeAfterClassTest extends \Codeception\TestCase\Test
{
    /**
     * @beforeClass
     */
    public static function setUpSomeSharedFixtures()
    {
        \Codeception\Module\OrderHelper::appendToFile('{');
    }

    public function testOne()
    {
        \Codeception\Module\OrderHelper::appendToFile('1');
    }

    public function testTwo()
    {
        \Codeception\Module\OrderHelper::appendToFile('2');
    }

    /**
     * @afterClass
     */
    public static function tearDownSomeSharedFixtures()
    {
        \Codeception\Module\OrderHelper::appendToFile('}');
    }

}