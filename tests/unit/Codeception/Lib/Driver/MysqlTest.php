<?php

use \Codeception\Lib\Driver\Db;
use \Codeception\Test\Unit;

/**
 * @group appveyor
 * @group db
 */
class MysqlTest extends Unit
{
    protected static $config = [
        'dsn' => 'mysql:host=localhost;dbname=codeception_test',
        'user' => 'root',
        'password' => ''
    ];

    protected static $sql;
    /**
     * @var \Codeception\Lib\Driver\MySql
     */
    protected $mysql;
    
    public static function setUpBeforeClass()
    {
        if (getenv('APPVEYOR')) {
            self::$config['password'] = 'Password12!';
        }
        $sql = file_get_contents(\Codeception\Configuration::dataDir() . '/dumps/mysql.sql');
        $sql = preg_replace('%/\*(?:(?!\*/).)*\*/%s', "", $sql);
        self::$sql = explode("\n", $sql);
        try {
            $mysql = Db::create(self::$config['dsn'], self::$config['user'], self::$config['password']);
            $mysql->cleanup();
        } catch (\Exception $e) {
        }
    }

    public function setUp()
    {
        try {
            $this->mysql = Db::create(self::$config['dsn'], self::$config['user'], self::$config['password']);
        } catch (\Exception $e) {
            $this->markTestSkipped('Couldn\'t establish connection to database: ' . $e->getMessage());
        }
        $this->mysql->cleanup();
        $this->mysql->load(self::$sql);
    }
    
    public function tearDown()
    {
        if (isset($this->mysql)) {
            $this->mysql->cleanup();
        }
    }

    public function testCleanupDatabase()
    {
        $this->assertNotEmpty($this->mysql->getDbh()->query("SHOW TABLES")->fetchAll());
        $this->mysql->cleanup();
        $this->assertEmpty($this->mysql->getDbh()->query("SHOW TABLES")->fetchAll());
    }

    /**
     * @group appveyor
     */
    public function testLoadDump()
    {
        $res = $this->mysql->getDbh()->query("select * from users where name = 'davert'");
        $this->assertNotEquals(false, $res);
        $this->assertGreaterThan(0, $res->rowCount());

        $res = $this->mysql->getDbh()->query("select * from groups where name = 'coders'");
        $this->assertNotEquals(false, $res);
        $this->assertGreaterThan(0, $res->rowCount());
    }

    public function testGetSingleColumnPrimaryKey()
    {
        $this->assertEquals(['id'], $this->mysql->getPrimaryKey('order'));
    }

    public function testGetCompositePrimaryKey()
    {
        $this->assertEquals(['group_id', 'id'], $this->mysql->getPrimaryKey('composite_pk'));
    }

    public function testGetEmptyArrayIfTableHasNoPrimaryKey()
    {
        $this->assertEquals([], $this->mysql->getPrimaryKey('no_pk'));
    }

    public function testGetPrimaryColumnOfTableUsingReservedWordAsTableName()
    {
        $this->assertEquals('id', $this->mysql->getPrimaryColumn('order'));
    }

    public function testGetPrimaryColumnThrowsExceptionIfTableHasCompositePrimaryKey()
    {
        $this->setExpectedException(
            '\Exception',
            'getPrimaryColumn method does not support composite primary keys, use getPrimaryKey instead'
        );
        $this->mysql->getPrimaryColumn('composite_pk');
    }

    public function testDeleteFromTableUsingReservedWordAsTableName()
    {
        $this->mysql->deleteQuery('order', 1);
        $res = $this->mysql->getDbh()->query("select id from `order` where id = 1");
        $this->assertEquals(0, $res->rowCount());
    }

    public function testDeleteFromTableUsingReservedWordAsPrimaryKey()
    {
        $this->mysql->deleteQuery('table_with_reserved_primary_key', 1, 'unique');
        $res = $this->mysql->getDbh()->query("select name from `table_with_reserved_primary_key` where `unique` = 1");
        $this->assertEquals(0, $res->rowCount());
    }

    public function testSelectWithBooleanParam()
    {
        $res = $this->mysql->executeQuery("select `id` from `users` where `is_active` = ?", [false]);
        $this->assertEquals(1, $res->rowCount());
    }

    public function testInsertIntoBitField()
    {
        if (getenv('WERCKER_ROOT')) {
            $this->markTestSkipped('Disabled on Wercker CI');
        }
        $res = $this->mysql->executeQuery(
            "insert into `users`(`id`,`name`,`email`,`is_active`,`created_at`) values (?,?,?,?,?)",
            [5,'insert.test','insert.test@mail.ua',false,'2012-02-01 21:17:47']
        );
        $this->assertEquals(1, $res->rowCount());
    }

    /**
     * THis will fail if MariaDb is used
     */
    public function testLoadThrowsExceptionWhenDumpFileContainsSyntaxError()
    {
        $sql = "INSERT INTO `users` (`name`) VALS('')";
        $expectedMessage = 'You have an error in your SQL syntax; ' .
            'check the manual that corresponds to your MySQL server version for the right syntax to use near ' .
            "'VALS('')' at line 1\nSQL query being executed: \n" . $sql;
        $this->setExpectedException('Codeception\Exception\ModuleException', $expectedMessage);
        $this->mysql->load([$sql]);
    }
}
