<?php

use Codeception\Lib\Driver\Db;

abstract class TestsForDb extends \Codeception\Test\Unit
{
    /**
     * @var \Codeception\Module\Db
     */
    protected $module;

    abstract public function getConfig();
    abstract public function getPopulator();

    protected function setUp()
    {
        $config = $this->getConfig();
        Db::create($config['dsn'], $config['user'], $config['password'])->cleanup();

        $this->module = new \Codeception\Module\Db(make_container(), $config);
        $this->module->_beforeSuite();
        $this->module->_before(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
        $this->assertTrue($this->module->isPopulated());
    }

    protected function tearDown()
    {
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

    public function testCleanupDatabase()
    {
        $this->module->seeInDatabase('users', ['name' => 'davert']);
        $this->module->_cleanup();

        // Since table does not exist it should fail
        // TODO: Catch this exception at the driver level and re-throw a general one
        // just for "table not found" across all the drivers
        $this->setExpectedException('PDOException');

        $this->module->dontSeeInDatabase('users', ['name' => 'davert']);
    }

    public function testHaveAndSeeInDatabase()
    {
        $userId = $this->module->haveInDatabase('users', ['name' => 'john', 'email' => 'john@jon.com']);
        $this->module->haveInDatabase('groups', ['name' => 'john', 'enabled' => false]);
        $this->assertInternalType('integer', $userId);
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

    public function testGrabFromDatabase()
    {
        $email = $this->module->grabFromDatabase('users', 'email', ['name' => 'davert']);
        $this->assertEquals('davert@mail.ua', $email);
    }

    public function testGrabNumRecords()
    {
        $num = $this->module->grabNumRecords('users', ['name' => 'davert']);
        $this->assertEquals($num, 1);
        $num = $this->module->grabNumRecords('users', ['name' => 'davert', 'email' => 'xxx@yyy.zz']);
        $this->assertEquals($num, 0);
        $num = $this->module->grabNumRecords('users', ['name' => 'user1']);
        $this->assertEquals($num, 0);
    }


    public function testLoadWithPopulator()
    {
        $this->module->_cleanup();
        $this->assertFalse($this->module->isPopulated());
        try {
            $this->module->seeInDatabase('users', ['name' => 'davert']);
        } catch (\PDOException $noTable) {
            $noTable;
            // No table was found...
        }
        $this->module->_reconfigure(
            [
                'populate'  => true,
                'populator' => $this->getPopulator(),
                'cleanup'   => true,
            ]
        );
        $this->module->_loadDump();
        $this->assertTrue($this->module->isPopulated());
        $this->module->seeInDatabase('users', ['name' => 'davert']);
    }
    
    public function testUpdateInDatabase()
    {
        $this->module->seeInDatabase('users', ['name' => 'davert']);
        $this->module->dontSeeInDatabase('users', ['name' => 'user1']);
        
        $this->module->updateInDatabase('users', ['name' => 'user1'], ['name' => 'davert']);
        
        $this->module->dontSeeInDatabase('users', ['name' => 'davert']);
        $this->module->seeInDatabase('users', ['name' => 'user1']);
        
        $this->module->updateInDatabase('users', ['name' => 'davert'], ['name' => 'user1']);
    }

    public function testInsertInDatabase()
    {
        $testData = ['status' => 'test'];
        $this->module->_insertInDatabase('no_pk', $testData);
        $this->module->seeInDatabase('no_pk', $testData);
        $this->module->_reconfigure(['cleanup' => false]);
        $this->module->_after(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));

        $this->module->_before(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
        $this->module->seeInDatabase('no_pk', $testData);
    }

}
