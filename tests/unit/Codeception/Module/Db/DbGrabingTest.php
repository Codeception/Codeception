<?php

abstract class DbGrabingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Codeception\Module\Db
     */
    protected $module;

    abstract public function getConfig();

    protected function setUp()
    {
        $this->module = new \Codeception\Module\Db(make_container(), $this->getConfig());
        $this->module->_beforeSuite();
        $this->module->_before(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
        $this->assertTrue($this->module->isPopulated());
    }

    protected function tearDown()
    {
        $this->module->_after(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
    }

    public function testGrabFromDatabase()
    {
        $email = $this->module->grabFromDatabase('users', 'email', ['name' => 'davert']);
        $this->assertEquals('davert@mail.ua', $email);
    }

    public function testGrabNumRecords()
    {
        $num = $this->module->grabNumRecords('users', ['name' => 'davert']);
        $this->assertEquals($num, 1);
        $num = $this->module->grabNumRecords('users', ['name' => 'davert', 'email' => 'xxx@yyy.zz']);
        $this->assertEquals($num, 0);
        $num = $this->module->grabNumRecords('users', ['name' => 'user1']);
        $this->assertEquals($num, 0);
    }
}
