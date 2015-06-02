<?php

use \Codeception\Lib\Driver\Db;

/**
 * @group appveyor
 */
class MysqlTest extends \PHPUnit_Framework_TestCase
{
    protected static $config = [
        'dsn' => 'mysql:host=localhost;dbname=codeception_test',
        'user' => 'root',
        'password' => ''
    ];

    protected static $mysql;
    protected static $sql;
    
    public static function setUpBeforeClass()
    {
        if (getenv('APPVEYOR')) {
            self::$config['password'] = 'Password12!';
        }
        $sql = file_get_contents(\Codeception\Configuration::dataDir() . '/dumps/mysql.sql');
        $sql = preg_replace('%/\*(?:(?!\*/).)*\*/%s', "", $sql);
        self::$sql = explode("\n", $sql);
        try {
            self::$mysql = Db::create(self::$config['dsn'], self::$config['user'], self::$config['password']);
            self::$mysql->cleanup();
        } catch (\Exception $e) {
        }
        
    }

    public function setUp()
    {
        if (!isset(self::$mysql)) {
            $this->markTestSkipped('Coudn\'t establish connection to database');
        }
        self::$mysql->load(self::$sql);
    }
    
    public function tearDown()
    {
        if (isset(self::$mysql)) {
            self::$mysql->cleanup();
        }
    }

    public function testCleanupDatabase()
    {
        $this->assertNotEmpty(self::$mysql->getDbh()->query("SHOW TABLES")->fetchAll());
        self::$mysql->cleanup();
        $this->assertEmpty(self::$mysql->getDbh()->query("SHOW TABLES")->fetchAll());
    }

    /**
     * @group appveyor
     */
    public function testLoadDump()
    {
        $res = self::$mysql->getDbh()->query("select * from users where name = 'davert'");
        $this->assertNotEquals(false, $res);
        $this->assertGreaterThan(0, $res->rowCount());

        $res = self::$mysql->getDbh()->query("select * from groups where name = 'coders'");
        $this->assertNotEquals(false, $res);
        $this->assertGreaterThan(0, $res->rowCount());
    }

    public function testGetPrimaryKeyOfTableUsingReservedWordAsTableName()
    {
        $this->assertEquals('id', self::$mysql->getPrimaryColumn('order'));
    }

    public function testDeleteFromTableUsingReservedWordAsTableName()
    {
        self::$mysql->deleteQuery('order', 1);
        $res = self::$mysql->getDbh()->query("select id from `order` where id = 1");
        $this->assertEquals(0, $res->rowCount());
    }

    public function testDeleteFromTableUsingReservedWordAsPrimaryKey()
    {
        self::$mysql->deleteQuery('table_with_reserved_primary_key', 1, 'unique');
        $res = self::$mysql->getDbh()->query("select name from `table_with_reserved_primary_key` where `unique` = 1");
        $this->assertEquals(0, $res->rowCount());
    }
}
