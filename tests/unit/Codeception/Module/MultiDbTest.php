<?php

use Codeception\Module\MultiDb;
use Codeception\Lib\ModuleContainer;
use Codeception\Lib\Di;

class MultiDbTest extends \PHPUnit_Framework_TestCase
{
    protected static $config = [
        'connections' => [
            'primary' => [
                'dsn' => 'sqlite:tests/data/dbtest.db',
                'user' => 'root',
                'password' => '',
                'cleanup' => false
            ]
        ]
    ];

    /**
     * @var MultiDb
     */
    protected static $module;

    public static function setUpBeforeClass()
    {
        $container = new ModuleContainer(new Di, self::$config);

        self::$module = new MultiDb($container);
        self::$module->_setConfig(self::$config);
        self::$module->_initialize();

        $sqlite = self::$module->drivers['primary'];
        $sqlite->cleanup();
        if (version_compare(PHP_VERSION, '5.5.0', '<')) {
            $dumpFile = '/dumps/sqlite-54.sql';
        } else {
            $dumpFile = '/dumps/sqlite.sql';
        }
        $sql = file_get_contents(\Codeception\Configuration::dataDir() . $dumpFile);
        $sql = preg_replace('%/\*(?:(?!\*/).)*\*/%s', "", $sql);
        $sql = explode("\n", $sql);
        $sqlite->load($sql);
    }

    public function testSeeInDatabase()
    {
        self::$module->amConnectedToDatabase('primary');
        self::$module->seeInDatabase('users', ['name' => 'davert']);
    }

    public function testDontSeeInDatabase()
    {
        self::$module->amConnectedToDatabase('primary');
        self::$module->dontSeeInDatabase('users', ['name' => 'user1']);
    }

    public function testCountInDatabase()
    {
        self::$module->amConnectedToDatabase('primary');
        self::$module->seeNumRecords(1, 'users', ['name' => 'davert']);
        self::$module->seeNumRecords(0, 'users', ['name' => 'davert', 'email' => 'xxx@yyy.zz']);
        self::$module->seeNumRecords(0, 'users', ['name' => 'user1']);
    }

    public function testGrabFromDatabase()
    {
        self::$module->amConnectedToDatabase('primary');
        $email = self::$module->grabFromDatabase('users', 'email', ['name' => 'davert']);
        $this->assertEquals('davert@mail.ua', $email);
    }

    public function testGrabNumRecords()
    {
        self::$module->amConnectedToDatabase('primary');
        $num = self::$module->grabNumRecords('users', ['name' => 'davert']);
        $this->assertEquals($num, 1);
        $num = self::$module->grabNumRecords('users', ['name' => 'davert', 'email' => 'xxx@yyy.zz']);
        $this->assertEquals($num, 0);
        $num = self::$module->grabNumRecords('users', ['name' => 'user1']);
        $this->assertEquals($num, 0);
    }
}