<?php
use \Codeception\Lib\Driver\Db;

/**
 * @group appveyor
 */
class PostgresTest extends \PHPUnit_Framework_TestCase
{
    protected static $config = [
        'dsn' => 'pgsql:host=localhost;dbname=codeception_test',
        'user' => 'postgres',
        'password' => ''
    ];

    protected static $postgres;
    protected static $sql;
    
    public static function setUpBeforeClass()
    {
        if (!function_exists('pg_connect')) {
            return;
        }
        if (getenv('APPVEYOR')) {
            self::$config['password'] = 'Password12!';
        }
        $sql = file_get_contents(\Codeception\Configuration::dataDir() . '/dumps/postgres.sql');
        $sql = preg_replace('%/\*(?:(?!\*/).)*\*/%s', "", $sql);
        self::$sql = explode("\n", $sql);
        try {
            self::$postgres = Db::create(self::$config['dsn'], self::$config['user'], self::$config['password']);
            self::$postgres->cleanup();
        } catch (\Exception $e) {
        }
        
    }

    public function setUp()
    {
        if (!isset(self::$postgres)) {
            if (!function_exists('pg_connect')) {
                $this->markTestSkipped("Postgres extension is not loaded");
            } else {
                $this->markTestSkipped('Coudn\'t establish connection to database');
            }
        }
        self::$postgres->load(self::$sql);
    }
    
    public function tearDown()
    {
        if (isset(self::$postgres)) {
            self::$postgres->cleanup();
        }
    }


    public function testCleanupDatabase()
    {
        $this->assertNotEmpty(self::$postgres->getDbh()->query("SELECT * FROM pg_tables where schemaname = 'public'")->fetchAll());
        self::$postgres->cleanup();
        $this->assertEmpty(self::$postgres->getDbh()->query("SELECT * FROM pg_tables where schemaname = 'public'")->fetchAll());
    }

    public function testLoadDump()
    {
        $res = self::$postgres->getDbh()->query("select * from users where name = 'davert'");
        $this->assertNotEquals(false, $res);
        $this->assertGreaterThan(0, $res->rowCount());

        $res = self::$postgres->getDbh()->query("select * from groups where name = 'coders'");
        $this->assertNotEquals(false, $res);
        $this->assertGreaterThan(0, $res->rowCount());

        $res = self::$postgres->getDbh()->query("select * from users where email = 'user2@example.org'");
        $this->assertNotEquals(false, $res);
        $this->assertGreaterThan(0, $res->rowCount());
    }

    public function testSelectWithEmptyCriteria()
    {
      $emptyCriteria = [];
      $generatedSql = self::$postgres->select('test_column', 'test_table', $emptyCriteria);

      $this->assertNotContains('where', $generatedSql);
    }

}
