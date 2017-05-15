<?php

abstract class DbGrabingTest extends \PHPUnit_Framework_TestCase
{

    public static $config = [];

    /**
     * @var \Codeception\Module\Db
     */
    protected static $module;

    public static function setUpBeforeClass()
    {
        self::$module = new \Codeception\Module\Db(make_container(), self::$config);
        self::$module->_beforeSuite();
    }

    protected function setUp()
    {
        self::$module->_before(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
        $this->assertTrue(self::$module->isPopulated());
    }

    protected function tearDown()
    {
        self::$module->_after(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
    }

    public function testGrabFromDatabase()
    {
        $email = self::$module->grabFromDatabase('users', 'email', ['name' => 'davert']);
        $this->assertEquals('davert@mail.ua', $email);
    }

    public function testGrabNumRecords()
    {
        $num = self::$module->grabNumRecords('users', ['name' => 'davert']);
        $this->assertEquals($num, 1);
        $num = self::$module->grabNumRecords('users', ['name' => 'davert', 'email' => 'xxx@yyy.zz']);
        $this->assertEquals($num, 0);
        $num = self::$module->grabNumRecords('users', ['name' => 'user1']);
        $this->assertEquals($num, 0);
    }
}
