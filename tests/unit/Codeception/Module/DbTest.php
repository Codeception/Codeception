<?php

class DbTest extends \PHPUnit_Framework_TestCase
{
    protected static $config = [
        'dsn' => 'sqlite:tests/data/dbtest.db',
        'user' => 'root',
        'password' => '',
        'cleanup' => true,
    ];

    /**
     * @var \Codeception\Module\Db
     */
    protected static $module;

    public static function setUpBeforeClass()
    {
        self::$module = new \Codeception\Module\Db(make_container());
        if (version_compare(PHP_VERSION, '5.5.0', '<')) {
            $dumpFile = 'dumps/sqlite-54.sql';
        } else {
            $dumpFile = 'dumps/sqlite.sql';
        }
        self::$module->_setConfig(
            self::$config
            + ['dump' => 'tests/data/' . $dumpFile]
        );
        self::$module->_beforeSuite();
    }

    protected function setUp()
    {
        self::$module->_before(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
    }

    protected function tearDown()
    {
        self::$module->_after(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
    }

    public function testDumpToolCommandInterpolatesVariables()
    {
        $ref = new \ReflectionObject(self::$module);
        $buildDumpToolCommand = $ref->getMethod('buildDumpToolCommand');
        $buildDumpToolCommand->setAccessible(true);

        $commandBuilt = $buildDumpToolCommand->invokeArgs(
            self::$module,
            [
                'mysql -u $user -h $host -D $dbname < $dump',
                [
                    'dsn' => 'mysql:host=127.0.0.1;dbname=my_db',
                    'dump' => 'tests/data/dumps/sqlite.sql',
                    'user' => 'root',
                ]
            ]
        );
        $this->assertEquals(
            'mysql -u root -h 127.0.0.1 -D my_db < tests/data/dumps/sqlite.sql',
            $commandBuilt
        );
    }

    public function testDumpToolCommandWontTouchVariablesNotFound()
    {
        $ref = new \ReflectionObject(self::$module);
        $buildDumpToolCommand = $ref->getMethod('buildDumpToolCommand');
        $buildDumpToolCommand->setAccessible(true);

        $commandBuilt = $buildDumpToolCommand->invokeArgs(
            self::$module,
            [
                'dumb_tool -u $user -h $host -D $dbname < $dump',
            ]
        );
        $this->assertEquals(
            'dumb_tool -u root -h $host -D $dbname < tests/data/dumps/sqlite.sql',
            $commandBuilt
        );

    }

}
