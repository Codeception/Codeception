<?php

use Codeception\Util\Stub;

class MongoDbTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Codeception\Module\MongoDb
     */
    protected $module;

    /**
     * @var \MongoDb
     */
    protected $db;

    protected function setUp()
    {
        if (!class_exists('\Mongo')) $this->markTestSkipped('Mongo is not installed');

        $mongo = new \Mongo();

        $this->module = new \Codeception\Module\MongoDb();
        $this->module->_setConfig(array(
                'dsn' => 'mongodb://localhost:27017/test',
                'user' => '',
                'password' => ''
        ));
        $this->module->_initialize();

        $this->db = $mongo->selectDB('test');
        $userCol = $this->db->createCollection('users');
        $userCol->insert(array('id' => 1, 'email' => 'miles@davis.com'));
    }

    protected function tearDown()
    {
        $this->db->dropCollection('users');
    }

    public function testSeeInCollection()
    {
        $this->module->seeInCollection('users', array('email' => 'miles@davis.com'));
    }

    public function testDontSeeInCollection()
    {
        $this->module->dontSeeInCollection('users', array('email' => 'davert@davert.com'));
    }

    public function testGrabFromCollection()
    {
        $user = $this->module->grabFromCollection('users', array('id' => 1));
        $this->assertTrue(isset($user['email']));
        $this->assertEquals('miles@davis.com',$user['email']);
    }

}