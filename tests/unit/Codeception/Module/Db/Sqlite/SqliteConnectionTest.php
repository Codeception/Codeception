<?php

require_once \Codeception\Configuration::testsDir().'unit/Codeception/Module/Db/DbConnectionTest.php';

use \Codeception\Lib\Driver\Db;
use \Codeception\Test\Unit;

class SqliteConnectionTest extends DbConnectionTest
{
    public static function setUpBeforeClass()
    {
        if (version_compare(PHP_VERSION, '5.5.0', '<')) {
            $dumpFile = 'dumps/sqlite-54.sql';
        } else {
            $dumpFile = 'dumps/sqlite.sql';
        }

        self::$config = [
            'dsn' => 'sqlite:tests/data/sqlite.db',
            'user' => 'root',
            'password' => '',
            'dump' => 'tests/data/' . $dumpFile,
            'reconnect' => true,
            'cleanup' => true,
        ];
        parent::setUpBeforeClass();
    }

    public function testConnectionIsKeptForTheWholeSuite()
    {
        $this->markTestSkipped('Sqlite will always have to reconnect when cleaning up.');
    }
}
