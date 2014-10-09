<?php

use \Codeception\Lib\Driver\Db;

class SqliteTest extends \PHPUnit_Framework_TestCase
{
    protected $config = array(
        'dsn' => 'sqlite:tests/data/sqlite.db',
        'user' => 'root',
        'password' => ''
    );

    protected $dbh;

    protected $sqlite;

    public function setUp()
    {
        $sql = file_get_contents(\Codeception\Configuration::dataDir() . '/dumps/sqlite.sql');
        $sql = preg_replace('%/\*(?:(?!\*/).)*\*/%s', "", $sql);
        $this->sql = explode("\n", $sql);

        $this->sqlite = Db::create($this->config['dsn'], $this->config['user'], $this->config['password']);
        $this->sqlite->cleanup();
    }
    
    
    public function testCleanupDatabase() {

        $this->sqlite->getDbh()->exec('CREATE TABLE "groups" ("id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL , "name" VARCHAR, "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP);');
        $this->assertGreaterThan(0, count($this->sqlite->getDbh()->query('SELECT name FROM sqlite_master WHERE type = "table";')->fetchAll()));
        $this->sqlite->cleanup();
        $this->assertEmpty($this->sqlite->getDbh()->query('SELECT name FROM sqlite_master WHERE type = "table";')->fetchAll());
    }
    
    public function testLoadDump() {
        $this->sqlite->load($this->sql);
        $res = $this->sqlite->getDbh()->query("select * from users where name = 'davert'");
        $this->assertNotEquals(false, $res);
        $this->assertNotEmpty($res->fetchAll());

        $res = $this->sqlite->getDbh()->query("select * from groups where name = 'coders'");
        $this->assertNotEquals(false, $res);
        $this->assertNotEmpty($res->fetchAll());

        $this->sqlite->cleanup();
        $this->sqlite->load($this->sql);

        $res = $this->sqlite->getDbh()->query("select * from users where name = 'davert'");
        $this->assertNotEquals(false, $res);
        $this->assertNotEmpty($res->fetchAll());
    }

}
