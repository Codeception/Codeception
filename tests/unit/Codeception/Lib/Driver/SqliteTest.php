<?php

use \Codeception\Lib\Driver\Db;

class SqliteTest extends \PHPUnit_Framework_TestCase
{
    protected static $config = array(
        'dsn' => 'sqlite:tests/data/sqlite.db',
        'user' => 'root',
        'password' => ''
    );

    protected static $sqlite;
    protected static $sql;
    
    public static function setUpBeforeClass()
    {
        $sql = file_get_contents(\Codeception\Configuration::dataDir() . '/dumps/sqlite.sql');
        $sql = preg_replace('%/\*(?:(?!\*/).)*\*/%s', "", $sql);
        self::$sql = explode("\n", $sql);
        try {
            self::$sqlite = Db::create(self::$config['dsn'], self::$config['user'], self::$config['password']);
            self::$sqlite->cleanup();
        } catch (\Exception $e) {
        }
        
    }

    public function setUp()
    {
        if (!isset(self::$sqlite)) {
            $this->markTestSkipped('Coudn\'t establish connection to database');
        }
        self::$sqlite->load(self::$sql);
    }
    
    public function tearDown()
    {
        if (isset(self::$sqlite)) {
            self::$sqlite->cleanup();
        }
    }
    
    
    public function testCleanupDatabase()
    {
        $this->assertGreaterThan(0, count(self::$sqlite->getDbh()->query('SELECT name FROM sqlite_master WHERE type = "table";')->fetchAll()));
        self::$sqlite->cleanup();
        $this->assertEmpty(self::$sqlite->getDbh()->query('SELECT name FROM sqlite_master WHERE type = "table";')->fetchAll());
    }
    
    public function testLoadDump()
    {
        $res = self::$sqlite->getDbh()->query("select * from users where name = 'davert'");
        $this->assertNotEquals(false, $res);
        $this->assertNotEmpty($res->fetchAll());

        $res = self::$sqlite->getDbh()->query("select * from groups where name = 'coders'");
        $this->assertNotEquals(false, $res);
        $this->assertNotEmpty($res->fetchAll());
    }
}
