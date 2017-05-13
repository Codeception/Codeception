<?php

use \Codeception\Lib\Driver\Db;
use \Codeception\Test\Unit;

/**
 * @group appveyor
 */
class PostgresTest extends Unit
{
    protected static $config = [
        'dsn' => 'pgsql:host=localhost;dbname=codeception_test',
        'user' => 'postgres',
        'password' => null,
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
        $dumpFile = 'dumps/postgres.sql';
        if (defined('HHVM_VERSION')) {
            $dumpFile = 'dumps/postgres-hhvm.sql';
        }
        $sql = file_get_contents(codecept_data_dir($dumpFile));
        $sql = preg_replace('%/\*(?:(?!\*/).)*\*/%s', '', $sql);
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
            $this->markTestSkipped('Coudn\'t establish connection to database: ' . $e->getMessage());
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
        $this->assertNotEmpty(
            $this->postgres->getDbh()->query("SELECT * FROM pg_tables where schemaname = 'public'")->fetchAll()
        );
        $this->postgres->cleanup();
        $this->assertEmpty(
            $this->postgres->getDbh()->query("SELECT * FROM pg_tables where schemaname = 'public'")->fetchAll()
        );
    }

    public function testCleanupDatabaseDeletesTypes()
    {
        $customTypes = ['composite_type', 'enum_type', 'range_type', 'base_type'];
        foreach ($customTypes as $customType) {
            $this->assertNotEmpty(
                $this->postgres->getDbh()
                    ->query("SELECT 1 FROM pg_type WHERE typname = '" . $customType . "';")
                    ->fetchAll()
            );
        }
        $this->postgres->cleanup();
        foreach ($customTypes as $customType) {
            $this->assertEmpty(
                $this->postgres->getDbh()
                    ->query("SELECT 1 FROM pg_type WHERE typname = '" . $customType . "';")
                    ->fetchAll()
            );
        }
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

        $res = $this->postgres->getDbh()
            ->query("select * from anotherschema.users where email = 'schemauser@example.org'");
        $this->assertEquals(1, $res->rowCount());
    }

    public function testSelectWithEmptyCriteria()
    {
        $emptyCriteria = [];
        $generatedSql = $this->postgres->select('test_column', 'test_table', $emptyCriteria);

        $this->assertNotContains('where', $generatedSql);
    }

    public function testGetSingleColumnPrimaryKey()
    {
        $this->assertEquals(['id'], $this->postgres->getPrimaryKey('order'));
    }

    public function testGetCompositePrimaryKey()
    {
        $this->assertEquals(['group_id', 'id'], $this->postgres->getPrimaryKey('composite_pk'));
    }

    public function testGetEmptyArrayIfTableHasNoPrimaryKey()
    {
        $this->assertEquals([], $this->postgres->getPrimaryKey('no_pk'));
    }

    public function testLastInsertIdReturnsSequenceValueWhenNonStandardSequenceNameIsUsed()
    {
        $this->postgres->executeQuery('INSERT INTO seqnames(name) VALUES(?)',['test']);
        $this->assertEquals(1, $this->postgres->lastInsertId('seqnames'));
    }

    public function testGetPrimaryColumnOfTableUsingReservedWordAsTableName()
    {
        $this->assertEquals('id', $this->postgres->getPrimaryColumn('order'));
    }

    public function testGetPrimaryColumnThrowsExceptionIfTableHasCompositePrimaryKey()
    {
        $this->setExpectedException(
            '\Exception',
            'getPrimaryColumn method does not support composite primary keys, use getPrimaryKey instead'
        );
        $this->postgres->getPrimaryColumn('composite_pk');
    }

    /**
     * @issue https://github.com/Codeception/Codeception/issues/4059
     */
    public function testLoadDumpEndingWithoutDelimiter()
    {
        $newDriver = new \Codeception\Lib\Driver\PostgreSql(self::$config['dsn'], self::$config['user'], self::$config['password']);
        $newDriver->load(['INSERT INTO empty_table VALUES(1, \'test\')']);
        $res = $newDriver->getDbh()->query("select * from empty_table where field = 'test'");
        $this->assertNotEquals(false, $res);
        $this->assertNotEmpty($res->fetchAll());
    }
}
