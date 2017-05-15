<?php

require_once \Codeception\Configuration::testsDir().'unit/Codeception/Module/Db/DbLoadingTest.php';

use \Codeception\Lib\Driver\Db;
use \Codeception\Test\Unit;

class SqliteLoadingTest extends DbLoadingTest
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
            'populate' => false,
        ];
    }
    public function getPopulator()
    {
        $config = $this->getConfig();
        return 'cat '. $config['dump'] .' | sqlite3 tests/data/sqlite.db';
    }
}
