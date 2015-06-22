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

    protected static $sql;
    protected $postgres;
    
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
            $postgres = Db::create(self::$config['dsn'], self::$config['user'], self::$config['password']);
            $postgres->cleanup();
        } catch (\Exception $e) {
        }
        
    }

    public function setUp()
    {
        try {
            $this->postgres = Db::create(self::$config['dsn'], self::$config['user'], self::$config['password']);
        } catch (\Exception $e) {
            $this->markTestSkipped('Coudn\'t establish connection to database');
        }
        $this->postgres->load(self::$sql);
    }
    
    public function tearDown()
    {
        if (isset($this->postgres)) {
            $this->postgres->cleanup();
        }
    }


    public function testCleanupDatabase()
    {
        $this->assertNotEmpty($this->postgres->getDbh()->query("SELECT * FROM pg_tables where schemaname = 'public'")->fetchAll());
        $this->postgres->cleanup();
        $this->assertEmpty($this->postgres->getDbh()->query("SELECT * FROM pg_tables where schemaname = 'public'")->fetchAll());
    }

    public function testLoadDump()
    {
        $res = $this->postgres->getDbh()->query("select * from users where name = 'davert'");
        $this->assertNotEquals(false, $res);
        $this->assertGreaterThan(0, $res->rowCount());

        $res = $this->postgres->getDbh()->query("select * from groups where name = 'coders'");
        $this->assertNotEquals(false, $res);
        $this->assertGreaterThan(0, $res->rowCount());

        $res = $this->postgres->getDbh()->query("select * from users where email = 'user2@example.org'");
        $this->assertNotEquals(false, $res);
        $this->assertGreaterThan(0, $res->rowCount());
    }

    public function testSelectWithEmptyCriteria()
    {
      $emptyCriteria = [];
      $generatedSql = $this->postgres->select('test_column', 'test_table', $emptyCriteria);

      $this->assertNotContains('where', $generatedSql);
    }

}
