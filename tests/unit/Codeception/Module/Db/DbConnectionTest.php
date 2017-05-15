<?php

abstract class DbConnectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Codeception\Module\Db
     */
    protected $module;

    abstract public function getConfig();

    protected function setUp()
    {
        $this->module = new \Codeception\Module\Db(make_container(), $this->getConfig());
        $this->module->_beforeSuite();
        $this->module->_before(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
        $this->assertTrue($this->module->isPopulated());
    }

    protected function tearDown()
    {
        // The config has to be reseted because it is being mangled in test
        $this->module->_resetConfig();
        $this->module->_after(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
    }

    public function testConnectionIsKeptForTheWholeSuite()
    {
        $testCase1 = \Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface');
        $testCase2 = \Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface');

        $this->module->_reconfigure(['reconnect' => false]);
        $this->module->_beforeSuite();

        // Simulate a test that runs
        $this->module->_before($testCase1);
        // Save these object instances IDs
        $driverAndConn1 = [
            $this->module->driver,
            $this->module->dbh
        ];
        $this->module->_after($testCase1);

        // Simulate a second test that runs
        $this->module->_before($testCase2);
        $driverAndConn2 = [
            $this->module->driver,
            $this->module->dbh
        ];
        $this->module->_after($testCase2);
        $this->assertEquals($driverAndConn2, $driverAndConn1);

        $this->module->_afterSuite();
    }

    public function testConnectionIsResetOnEveryTestWhenReconnectIsTrue()
    {
        $testCase1 = \Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface');
        $testCase2 = \Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface');

        $this->module->_reconfigure(['reconnect' => true]);
        $this->module->_beforeSuite();

        // Simulate a test that runs
        $this->module->_before($testCase1);
        // Save these object instances IDs
        $driverAndConn1 = [
            $this->module->driver,
            $this->module->dbh
        ];
        $this->module->_after($testCase1);

        // Simulate a second test that runs
        $this->module->_before($testCase2);
        $driverAndConn2 = [
            $this->module->driver,
            $this->module->dbh
        ];
        $this->module->_after($testCase2);
        $this->assertNotEquals($driverAndConn2, $driverAndConn1);

        $this->module->_afterSuite();
    }
}
