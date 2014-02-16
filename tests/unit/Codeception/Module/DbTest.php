<?php
class DbTest extends \PHPUnit_Framework_TestCase
{
    protected $config = array(
        'dsn' => 'sqlite:tests/data/sqlite.db',
        'user' => 'root',
        'password' => '',
        'cleanup' => false
    );

    /**
     * @var \Codeception\Module\Db
     */
    protected $module = null;

    public function setUp()
    {
        $this->module = new \Codeception\Module\Db();
        $this->module->_setConfig($this->config);
        $this->module->_initialize();
        // $this->loadDump(); enable this when you want to change fixtures
    }

    protected function loadDump()
    {
        $sql = file_get_contents(\Codeception\Configuration::dataDir() . '/dumps/sqlite.sql');
        $sql = preg_replace('%/\*(?:(?!\*/).)*\*/%s', "", $sql);
        $sql = explode("\n", $sql);
        $sqlite = \Codeception\Lib\Driver\Db::create($this->config['dsn'], $this->config['user'], $this->config['password']);
        $sqlite->load($sql);
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
        $user_id = $this->module->haveInDatabase('users', array('name' => 'john', 'email' => 'john@jon.com'));
        $this->assertInternalType('integer', $user_id);
        $this->module->seeInDatabase('users', array('name' => 'john', 'email' => 'john@jon.com'));
        $this->module->_after(\Codeception\Util\Stub::make('\Codeception\TestCase'));
        $this->module->dontSeeInDatabase('users', array('name' => 'john'));
    }
}