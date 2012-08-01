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
    }

    public function testSeeInDatabase() {
        $this->module->seeInDatabase('users', array('name' => 'davert'));
    }

    public function testDontSeeInDatabase() {
        $this->module->dontSeeInDatabase('users', array('name' => 'user1'));
    }

    public function testGrabFromDatabase() {
        $email = $this->module->grabFromDatabase('users', 'email', array('name' => 'davert'));
        $this->assertEquals('davert@mail.ua', $email);
    }
}