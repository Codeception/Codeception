<?php

abstract class DbConnectionTest extends \PHPUnit_Framework_TestCase
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
        // The config has to be reseted because it is being mangled in test
        self::$module->_resetConfig();
        self::$module->_after(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
    }

    public function testConnectionIsKeptForTheWholeSuite()
    {
        $testCase1 = \Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface');
        $testCase2 = \Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface');

        self::$module->_reconfigure(['reconnect' => false]);
        self::$module->_beforeSuite();

        // Simulate a test that runs
        self::$module->_before($testCase1);
        // Save these object instances IDs
        $driverAndConn1 = [
            self::$module->driver,
            self::$module->dbh
        ];
        self::$module->_after($testCase1);

        // Simulate a second test that runs
        self::$module->_before($testCase2);
        $driverAndConn2 = [
            self::$module->driver,
            self::$module->dbh
        ];
        self::$module->_after($testCase2);
        $this->assertEquals($driverAndConn2, $driverAndConn1);

        self::$module->_afterSuite();
    }

    public function testConnectionIsResetOnEveryTestWhenReconnectIsTrue()
    {
        $testCase1 = \Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface');
        $testCase2 = \Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface');

        self::$module->_reconfigure(['reconnect' => true]);
        self::$module->_beforeSuite();

        // Simulate a test that runs
        self::$module->_before($testCase1);
        // Save these object instances IDs
        $driverAndConn1 = [
            self::$module->driver,
            self::$module->dbh
        ];
        self::$module->_after($testCase1);

        // Simulate a second test that runs
        self::$module->_before($testCase2);
        $driverAndConn2 = [
            self::$module->driver,
            self::$module->dbh
        ];
        self::$module->_after($testCase2);
        $this->assertNotEquals($driverAndConn2, $driverAndConn1);

        self::$module->_afterSuite();
    }
}
