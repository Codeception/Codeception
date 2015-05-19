<?php
use \Codeception\Lib\Driver\Db;

/**
 * @group appveyor
 */
class PostgresTest extends \PHPUnit_Framework_TestCase
{
    protected $config = [
        'dsn' => 'pgsql:host=localhost;dbname=codeception_test',
        'user' => 'postgres',
        'password' => ''
    ];

    protected $dbh;

    protected $postgres;

    public function setUp()
    {
        if (!function_exists('pg_connect')) return $this->markTestSkipped("Postgres extensions not loaded");
        if (getenv('APPVEYOR')) {
            $this->config['password'] = 'Password12!';
        }
        $sql = file_get_contents(\Codeception\Configuration::dataDir() . '/dumps/postgres.sql');
        $sql = preg_replace('%/\*(?:(?!\*/).)*\*/%s', "", $sql);
        $this->sql = explode("\n", $sql);
        try {
            $this->postgres = Db::create($this->config['dsn'], $this->config['user'], $this->config['password']);
        } catch (\Exception $e) {
            $this->markTestSkipped('Coudn\'t establish connection to database');
            return;
        }
        $this->postgres->cleanup();
    }

    public function testCleanupDatabase() {

        $this->postgres->getDbh()->exec('
        CREATE TABLE groups
        (
          "name" character varying(50),
          created_at timestamp without time zone DEFAULT now(),
          id serial NOT NULL,
          CONSTRAINT g1 PRIMARY KEY (id)
        )
        WITH (
          OIDS=FALSE
        );
        ALTER TABLE groups OWNER TO postgres;
        ');

        $this->assertEquals(1, count($this->postgres->getDbh()->query("SELECT * FROM pg_tables where schemaname = 'public'")->fetchAll()));
        $this->postgres->cleanup();
        $this->assertEmpty($this->postgres->getDbh()->query("SELECT * FROM pg_tables where schemaname = 'public'")->fetchAll());
    }

    public function testLoadDump() {
        $this->postgres->load($this->sql);
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

    public function testSelectWithEmptyCriteria() {
      $emptyCriteria = [];
      $generatedSql = $this->postgres->select('test_column', 'test_table', $emptyCriteria);

      $this->assertNotContains('where', $generatedSql);

    }

}
