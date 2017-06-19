<?php

use \Codeception\Lib\Driver\Db;
use \Codeception\Test\Unit;

/**
 * @group db
 * Class SqliteTest
 */
class SqliteTest extends Unit
{
    protected static $config = array(
        'dsn' => 'sqlite:tests/data/sqlite.db',
        'user' => 'root',
        'password' => ''
    );

    /**
     * @var \Codeception\Lib\Driver\Sqlite
     */
    protected static $sqlite;
    protected static $sql;

    public static function setUpBeforeClass()
    {
        if (version_compare(PHP_VERSION, '5.5.0', '<')) {
            $dumpFile = '/dumps/sqlite-54.sql';
        } else {
            $dumpFile = '/dumps/sqlite.sql';
        }

        $sql = file_get_contents(\Codeception\Configuration::dataDir() . $dumpFile);
        $sql = preg_replace('%/\*(?:(?!\*/).)*\*/%s', "", $sql);
        self::$sql = explode("\n", $sql);
    }

    public function setUp()
    {
        self::$sqlite = Db::create(self::$config['dsn'], self::$config['user'], self::$config['password']);
        self::$sqlite->cleanup();
        self::$sqlite->load(self::$sql);
    }

    public function tearDown()
    {
        if (isset(self::$sqlite)) {
            self::$sqlite->cleanup();
        }
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

    public function testGetPrimaryKeyReturnsRowIdIfTableHasIt()
    {
        $this->assertEquals(['_ROWID_'], self::$sqlite->getPrimaryKey('groups'));
    }

    public function testGetPrimaryKeyReturnsRowIdIfTableHasNoPrimaryKey()
    {
        $this->assertEquals(['_ROWID_'], self::$sqlite->getPrimaryKey('no_pk'));
    }

    public function testGetSingleColumnPrimaryKeyWhenTableHasNoRowId()
    {
        if (version_compare(PHP_VERSION, '5.5.0', '<')) {
            $this->markTestSkipped('Sqlite does not support WITHOUT ROWID on travis');
        }
        $this->assertEquals(['id'], self::$sqlite->getPrimaryKey('order'));
    }

    public function testGetCompositePrimaryKeyWhenTableHasNoRowId()
    {
        if (version_compare(PHP_VERSION, '5.5.0', '<')) {
            $this->markTestSkipped('Sqlite does not support WITHOUT ROWID on travis');
        }
        $this->assertEquals(['group_id', 'id'], self::$sqlite->getPrimaryKey('composite_pk'));
    }

    public function testGetPrimaryColumnOfTableUsingReservedWordAsTableNameWhenTableHasNoRowId()
    {
        if (version_compare(PHP_VERSION, '5.5.0', '<')) {
            $this->markTestSkipped('Sqlite does not support WITHOUT ROWID on travis');
        }
        $this->assertEquals('id', self::$sqlite->getPrimaryColumn('order'));
    }

    public function testGetPrimaryColumnThrowsExceptionIfTableHasCompositePrimaryKey()
    {
        if (version_compare(PHP_VERSION, '5.5.0', '<')) {
            $this->markTestSkipped('Sqlite does not support WITHOUT ROWID on travis');
        }
        $this->setExpectedException(
            '\Exception',
            'getPrimaryColumn method does not support composite primary keys, use getPrimaryKey instead'
        );
        self::$sqlite->getPrimaryColumn('composite_pk');
    }

    public function testThrowsExceptionIfInMemoryDatabaseIsUsed()
    {
        $this->setExpectedException(
            '\Codeception\Exception\ModuleException',
            ':memory: database is not supported'
        );

        Db::create('sqlite::memory:', '', '');
    }

    /**
     * @issue https://github.com/Codeception/Codeception/issues/4059
     */
    public function testLoadDumpEndingWithoutDelimiter()
    {
        $newDriver = new \Codeception\Lib\Driver\Sqlite(self::$config['dsn'], '', '');
        $newDriver->load(['INSERT INTO empty_table VALUES(1, "test")']);
        $res = $newDriver->getDbh()->query("select * from empty_table where field = 'test'");
        $this->assertNotEquals(false, $res);
        $this->assertNotEmpty($res->fetchAll());
    }
}
