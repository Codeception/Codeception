<?php

abstract class DbHavingTest extends \PHPUnit_Framework_TestCase
{

    public static $config = [];

    /**
     * @var \Codeception\Module\Db
     */
    protected static $module;

    public static function setUpBeforeClass()
    {
        self::$module = new \Codeception\Module\Db(make_container(), self::$config);
        self::$module->_resetConfig();
        self::$module->_beforeSuite();
    }

    protected function setUp()
    {
        self::$module->_before(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
        $this->assertTrue(self::$module->isPopulated());
    }

    protected function tearDown()
    {
        self::$module->_resetConfig();
        self::$module->_after(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
    }

    public function testHaveAndSeeInDatabase()
    {
        $user_id = self::$module->haveInDatabase('users', ['name' => 'john', 'email' => 'john@jon.com']);
        $group_id = self::$module->haveInDatabase('groups', ['name' => 'john', 'enabled' => false]);
        $this->assertInternalType('integer', $user_id);
        self::$module->seeInDatabase('users', ['name' => 'john', 'email' => 'john@jon.com']);
        self::$module->dontSeeInDatabase('users', ['name' => 'john', 'email' => null]);
        self::$module->_after(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));

        self::$module->_before(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
        self::$module->dontSeeInDatabase('users', ['name' => 'john']);
    }

    public function testHaveInDatabaseWithCompositePrimaryKey()
    {
        if (version_compare(PHP_VERSION, '5.5.0', '<')) {
            $this->markTestSkipped('Does not support WITHOUT ROWID on travis');
        }

        $insertQuery = 'INSERT INTO composite_pk (group_id, id, status) VALUES (?, ?, ?)';
        //this test checks that module does not delete columns by partial primary key
        self::$module->driver->executeQuery($insertQuery, [1, 2, 'test']);
        self::$module->driver->executeQuery($insertQuery, [2, 1, 'test2']);
        $testData = ['id' => 2, 'group_id' => 2, 'status' => 'test3'];
        self::$module->haveInDatabase('composite_pk', $testData);
        self::$module->seeInDatabase('composite_pk', $testData);
        self::$module->_reconfigure(['cleanup' => false]);
        self::$module->_after(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));

        self::$module->_before(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
        self::$module->dontSeeInDatabase('composite_pk', $testData);
        self::$module->seeInDatabase('composite_pk', ['group_id' => 1, 'id' => 2, 'status' => 'test']);
        self::$module->seeInDatabase('composite_pk', ['group_id' => 2, 'id' => 1, 'status' => 'test2']);
    }

    public function testHaveInDatabaseWithoutPrimaryKey()
    {
        $testData = ['status' => 'test'];
        self::$module->haveInDatabase('no_pk', $testData);
        self::$module->seeInDatabase('no_pk', $testData);
        self::$module->_after(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));

        self::$module->_before(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
        self::$module->dontSeeInDatabase('no_pk', $testData);
    }
}
