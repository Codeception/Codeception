<?php

abstract class DbHavingTest extends \PHPUnit_Framework_TestCase
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
        $this->assertTrue($this->module->populated);
    }

    protected function tearDown()
    {
        $this->module->_resetConfig();
        $this->module->_after(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
    }

    public function testHaveAndSeeInDatabase()
    {
        $user_id = $this->module->haveInDatabase('users', ['name' => 'john', 'email' => 'john@jon.com']);
        $group_id = $this->module->haveInDatabase('groups', ['name' => 'john', 'enabled' => false]);
        $this->assertInternalType('integer', $user_id);
        $this->module->seeInDatabase('users', ['name' => 'john', 'email' => 'john@jon.com']);
        $this->module->dontSeeInDatabase('users', ['name' => 'john', 'email' => null]);
        $this->module->_after(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));

        $this->module->_before(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
        $this->module->dontSeeInDatabase('users', ['name' => 'john']);
    }

    public function testHaveInDatabaseWithCompositePrimaryKey()
    {
        if (version_compare(PHP_VERSION, '5.5.0', '<')) {
            $this->markTestSkipped('Does not support WITHOUT ROWID on travis');
        }

        $insertQuery = 'INSERT INTO composite_pk (group_id, id, status) VALUES (?, ?, ?)';
        //this test checks that module does not delete columns by partial primary key
        $this->module->driver->executeQuery($insertQuery, [1, 2, 'test']);
        $this->module->driver->executeQuery($insertQuery, [2, 1, 'test2']);
        $testData = ['id' => 2, 'group_id' => 2, 'status' => 'test3'];
        $this->module->haveInDatabase('composite_pk', $testData);
        $this->module->seeInDatabase('composite_pk', $testData);
        $this->module->_reconfigure(['cleanup' => false]);
        $this->module->_after(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));

        $this->module->_before(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
        $this->module->dontSeeInDatabase('composite_pk', $testData);
        $this->module->seeInDatabase('composite_pk', ['group_id' => 1, 'id' => 2, 'status' => 'test']);
        $this->module->seeInDatabase('composite_pk', ['group_id' => 2, 'id' => 1, 'status' => 'test2']);
    }

    public function testHaveInDatabaseWithoutPrimaryKey()
    {
        $testData = ['status' => 'test'];
        $this->module->haveInDatabase('no_pk', $testData);
        $this->module->seeInDatabase('no_pk', $testData);
        $this->module->_after(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));

        $this->module->_before(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
        $this->module->dontSeeInDatabase('no_pk', $testData);
    }
}
