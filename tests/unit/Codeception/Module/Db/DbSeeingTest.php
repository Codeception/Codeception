<?php

abstract class DbSeeingTest extends \PHPUnit_Framework_TestCase
{

    public static $config = [];

    /**
     * @var \Codeception\Module\Db
     */
    protected static $module;

    public static function setUpBeforeClass()
    {
        self::$module = new \Codeception\Module\Db(make_container(), self::$config);
        self::$module->_beforeSuite();
    }

    protected function setUp()
    {
        self::$module->_before(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
        $this->assertTrue(self::$module->isPopulated());
    }

    protected function tearDown()
    {
        self::$module->_after(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
    }

    public function testSeeInDatabase()
    {
        self::$module->seeInDatabase('users', ['name' => 'davert']);
    }

    public function testCountInDatabase()
    {
        self::$module->seeNumRecords(1, 'users', ['name' => 'davert']);
        self::$module->seeNumRecords(0, 'users', ['name' => 'davert', 'email' => 'xxx@yyy.zz']);
        self::$module->seeNumRecords(0, 'users', ['name' => 'user1']);
    }

    public function testDontSeeInDatabase()
    {
        self::$module->dontSeeInDatabase('users', ['name' => 'user1']);
    }

    public function testDontSeeInDatabaseWithEmptyTable()
    {
        self::$module->dontSeeInDatabase('empty_table');
    }
}
