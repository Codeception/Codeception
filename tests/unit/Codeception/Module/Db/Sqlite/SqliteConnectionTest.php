<?php

require_once \Codeception\Configuration::testsDir().'unit/Codeception/Module/Db/DbConnectionTest.php';

use \Codeception\Lib\Driver\Db;
use \Codeception\Test\Unit;

class SqliteConnectionTest extends DbConnectionTest
{
    public function getConfig()
    {
        if (version_compare(PHP_VERSION, '5.5.0', '<')) {
            $dumpFile = 'dumps/sqlite-54.sql';
        } else {
            $dumpFile = 'dumps/sqlite.sql';
        }

        return [
            'dsn' => 'sqlite:tests/data/sqlite.db',
            'user' => 'root',
            'password' => '',
            'dump' => 'tests/data/' . $dumpFile,
            'reconnect' => true,
            'cleanup' => true,
        ];
    }

    public function testConnectionIsKeptForTheWholeSuite()
    {
        $this->markTestSkipped('Sqlite will always have to reconnect when cleaning up.');
    }
}
