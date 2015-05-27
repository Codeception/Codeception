<?php

class DbTest extends \PHPUnit_Framework_TestCase
{
    protected static $config = array(
        'dsn' => 'sqlite:tests/data/sqlite.db',
        'user' => 'root',
        'password' => '',
        'cleanup' => false
    );

    /**
     * @var \Codeception\Module\Db
     */
    protected $module = null;
    
    public static function setUpBeforeClass()
    {
        try {
            $sqlite = \Codeception\Lib\Driver\Db::create(self::$config['dsn'], self::$config['user'], self::$config['password']);
            $sqlite->cleanup();
            
            $sql = file_get_contents(\Codeception\Configuration::dataDir() . '/dumps/sqlite.sql');
            $sql = preg_replace('%/\*(?:(?!\*/).)*\*/%s', "", $sql);
            $sql = explode("\n", $sql);
            $sqlite->load($sql);
        } catch (\Exception $e) {
        }
        
    }


    public function setUp()
    {
        $this->module = new \Codeception\Module\Db();
        $this->module->_setConfig(self::$config);
        $this->module->_initialize();
//        $this->loadDump(); // enable this when you want to change fixtures
    }

    public function testSeeInDatabase() {
        $this->module->seeInDatabase('users', array('name' => 'davert'));
    }

    public function testDontSeeInDatabase() {
        $this->module->dontSeeInDatabase('users', array('name' => 'user1'));
    }

    public function testDontSeeInDatabaseWithEmptyTable() {
        $this->module->dontSeeInDatabase('empty_table');
    }

    public function testGrabFromDatabase() {
        $email = $this->module->grabFromDatabase('users', 'email', array('name' => 'davert'));
        $this->assertEquals('davert@mail.ua', $email);
    }

    public function testHaveAndSeeInDatabase()
    {
        $this->module->_before(\Codeception\Util\Stub::make('\Codeception\TestCase'));
        $user_id = $this->module->haveInDatabase('users', array('name' => 'john', 'email' => 'john@jon.com'));
        $group_id = $this->module->haveInDatabase('groups', array('name' => 'john', 'enabled' => false));
        $this->assertInternalType('integer', $user_id);
        $this->module->seeInDatabase('users', array('name' => 'john', 'email' => 'john@jon.com'));
        $this->module->dontSeeInDatabase('users', array('name' => 'john', 'email' => null));
        $this->module->_after(\Codeception\Util\Stub::make('\Codeception\TestCase'));
        $this->module->dontSeeInDatabase('users', array('name' => 'john'));
    }
}