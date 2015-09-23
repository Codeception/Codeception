<?php

class DbTest extends \PHPUnit_Framework_TestCase
{
    protected static $config = array(
        'dsn' => 'sqlite:tests/data/dbtest.db',
        'user' => 'root',
        'password' => '',
        'cleanup' => false
    );

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
        $sql = file_get_contents(\Codeception\Configuration::dataDir() . '/dumps/sqlite.sql');
        $sql = preg_replace('%/\*(?:(?!\*/).)*\*/%s', "", $sql);
        $sql = explode("\n", $sql);
        $sqlite->load($sql);
    }

    public function testSeeInDatabase()
    {
        self::$module->seeInDatabase('users', array('name' => 'davert'));
    }

    public function testCountInDatabase()
    {
        self::$module->seeNumRecords(1, 'users', ['name' => 'davert']);
        self::$module->seeNumRecords(0, 'users', ['name' => 'davert', 'email' => 'xxx@yyy.zz']);
        self::$module->seeNumRecords(0, 'users', ['name' => 'user1']);
    }

    public function testDontSeeInDatabase()
    {
        self::$module->dontSeeInDatabase('users', array('name' => 'user1'));
    }

    public function testDontSeeInDatabaseWithEmptyTable()
    {
        self::$module->dontSeeInDatabase('empty_table');
    }

    public function testGrabFromDatabase()
    {
        $email = self::$module->grabFromDatabase('users', 'email', array('name' => 'davert'));
        $this->assertEquals('davert@mail.ua', $email);
    }

    public function testHaveAndSeeInDatabase()
    {
        self::$module->_before(\Codeception\Util\Stub::make('\Codeception\TestCase'));
        $user_id = self::$module->haveInDatabase('users', array('name' => 'john', 'email' => 'john@jon.com'));
        $group_id = self::$module->haveInDatabase('groups', array('name' => 'john', 'enabled' => false));
        $this->assertInternalType('integer', $user_id);
        self::$module->seeInDatabase('users', array('name' => 'john', 'email' => 'john@jon.com'));
        self::$module->dontSeeInDatabase('users', array('name' => 'john', 'email' => null));
        self::$module->_after(\Codeception\Util\Stub::make('\Codeception\TestCase'));
        self::$module->dontSeeInDatabase('users', array('name' => 'john'));
    }

    public function testReconnectOption()
    {
        $testCase1 = \Codeception\Util\Stub::make('\Codeception\TestCase');
        $testCase2 = \Codeception\Util\Stub::make('\Codeception\TestCase');

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
