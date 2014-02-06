<?php

use \Codeception\Lib\Driver\Db;

class MysqlTest extends \PHPUnit_Framework_TestCase
{
    protected $config = array(
        'dsn' => 'mysql:host=localhost;dbname=codeception_test',
        'user' => 'root',
        'password' => ''
    );

    protected $dbh;

    protected $mysql;

    public function setUp()
    {
        $sql = file_get_contents(\Codeception\Configuration::dataDir() . '/dumps/mysql.sql');
        $sql = preg_replace('%/\*(?:(?!\*/).)*\*/%s', "", $sql);
        $this->sql = explode("\n", $sql);
        try {
            $this->mysql = Db::create($this->config['dsn'], $this->config['user'], $this->config['password']);
        } catch (\Exception $e) {
            $this->markTestSkipped('Coudn\'t establish connection to database');
            return;
        }
        $this->mysql->cleanup();
    }


    public function testCleanupDatabase()
    {

        $this->mysql->getDbh()->exec("
        CREATE TABLE `groups` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(100) DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1
        ");
        $this->assertEquals(1, count($this->mysql->getDbh()->query("SHOW TABLES")->fetchAll()));
        $this->mysql->cleanup();
        $this->assertEmpty($this->mysql->getDbh()->query("SHOW TABLES")->fetchAll());
    }

    public function testLoadDump()
    {
        $this->mysql->load($this->sql);
        $res = $this->mysql->getDbh()->query("select * from users where name = 'davert'");
        $this->assertNotEquals(false, $res);
        $this->assertGreaterThan(0, $res->rowCount());

        $res = $this->mysql->getDbh()->query("select * from groups where name = 'coders'");
        $this->assertNotEquals(false, $res);
        $this->assertGreaterThan(0, $res->rowCount());
    }

}
