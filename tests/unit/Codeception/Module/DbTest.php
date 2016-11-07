<?php

class DbTest extends \PHPUnit_Framework_TestCase
{
    protected static $config = [
        'dsn' => 'sqlite:tests/data/dbtest.db',
        'user' => 'root',
        'password' => '',
        'cleanup' => false
    ];

    /**
     * @var \Codeception\Module\Db
     */
    protected static $module;

    public static function setUpBeforeClass()
    {
        self::$module = new \Codeception\Module\Db(make_container());
        self::$module->_setConfig(self::$config);
        self::$module->_initialize();

        $sqlite = self::$module->driver;
        $sqlite->cleanup();
        if (version_compare(PHP_VERSION, '5.5.0', '<')) {
            $dumpFile = '/dumps/sqlite-54.sql';
        } else {
            $dumpFile = '/dumps/sqlite.sql';
        }
        $sql = file_get_contents(\Codeception\Configuration::dataDir() . $dumpFile);
        $sql = preg_replace('%/\*(?:(?!\*/).)*\*/%s', "", $sql);
        $sql = explode("\n", $sql);
        $sqlite->load($sql);
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

    public function testGrabFromDatabase()
    {
        $email = self::$module->grabFromDatabase('users', 'email', ['name' => 'davert']);
        $this->assertEquals('davert@mail.ua', $email);
    }

    public function testGrabNumRecords()
    {
        $num = self::$module->grabNumRecords('users', ['name' => 'davert']);
        $this->assertEquals($num, 1);
        $num = self::$module->grabNumRecords('users', ['name' => 'davert', 'email' => 'xxx@yyy.zz']);
        $this->assertEquals($num, 0);
        $num = self::$module->grabNumRecords('users', ['name' => 'user1']);
        $this->assertEquals($num, 0);
    }

    public function testHaveAndSeeInDatabase()
    {
        self::$module->_before(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
        $user_id = self::$module->haveInDatabase('users', ['name' => 'john', 'email' => 'john@jon.com']);
        $group_id = self::$module->haveInDatabase('groups', ['name' => 'john', 'enabled' => false]);
        $this->assertInternalType('integer', $user_id);
        self::$module->seeInDatabase('users', ['name' => 'john', 'email' => 'john@jon.com']);
        self::$module->dontSeeInDatabase('users', ['name' => 'john', 'email' => null]);
        self::$module->_after(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
        self::$module->dontSeeInDatabase('users', ['name' => 'john']);
    }

    public function testHaveInDatabaseWithCompositePrimaryKey()
    {
        if (version_compare(PHP_VERSION, '5.5.0', '<')) {
            $this->markTestSkipped('Does not support WITHOUT ROWID on travis');
        }
        self::$module->_before(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
        $insertQuery = 'INSERT INTO composite_pk (group_id, id, status) VALUES(?, ?, ?)';
        //this test checks that module does not delete columns by partial primary key
        self::$module->driver->executeQuery($insertQuery, [1, 2, 'test']);
        self::$module->driver->executeQuery($insertQuery, [2, 1, 'test2']);
        $testData = ['id' => 2, 'group_id' => 2, 'status' => 'test3'];
        self::$module->haveInDatabase('composite_pk', $testData);
        self::$module->seeInDatabase('composite_pk', $testData);
        self::$module->_after(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
        self::$module->dontSeeInDatabase('composite_pk', $testData);
        self::$module->seeInDatabase('composite_pk', ['group_id' => 1, 'id' => 2, 'status' => 'test']);
        self::$module->seeInDatabase('composite_pk', ['group_id' => 2, 'id' => 1, 'status' => 'test2']);
    }

    public function testHaveInDatabaseWithoutPrimaryKey()
    {
        self::$module->_before(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
        $testData = ['status' => 'test'];
        self::$module->haveInDatabase('no_pk', $testData);
        self::$module->seeInDatabase('no_pk', $testData);
        self::$module->_after(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
        self::$module->dontSeeInDatabase('no_pk', $testData);
    }

    public function testReconnectOption()
    {
        $testCase1 = \Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface');
        $testCase2 = \Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface');

        self::$module->_reconfigure(['reconnect' => true]);
        $this->assertNotNull(self::$module->driver, 'driver is null before test');
        $this->assertNotNull(self::$module->dbh, 'dbh is null before test');

        self::$module->_after($testCase1);

        $this->assertNull(self::$module->driver, 'driver is not unset by _after');
        $this->assertNull(self::$module->dbh, 'dbh is not unset by _after');

        self::$module->_before($testCase2);

        $this->assertNotNull(self::$module->driver, 'driver is not set by _before');
        $this->assertNotNull(self::$module->dbh, 'dbh is not set by _before');

        self::$module->_reconfigure(['reconnect' => false]);
    }
}
