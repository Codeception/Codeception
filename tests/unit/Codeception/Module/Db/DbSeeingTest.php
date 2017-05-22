<?php

abstract class DbSeeingTest extends \PHPUnit_Framework_TestCase
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
        $this->module->_after(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
    }

    public function testSeeInDatabase()
    {
        $this->module->seeInDatabase('users', ['name' => 'davert']);
    }

    public function testCountInDatabase()
    {
        $this->module->seeNumRecords(1, 'users', ['name' => 'davert']);
        $this->module->seeNumRecords(0, 'users', ['name' => 'davert', 'email' => 'xxx@yyy.zz']);
        $this->module->seeNumRecords(0, 'users', ['name' => 'user1']);
    }

    public function testDontSeeInDatabase()
    {
        $this->module->dontSeeInDatabase('users', ['name' => 'user1']);
    }

    public function testDontSeeInDatabaseWithEmptyTable()
    {
        $this->module->dontSeeInDatabase('empty_table');
    }
}
