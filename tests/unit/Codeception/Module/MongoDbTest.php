<?php

use Codeception\Module\MongoDb;

class MongoDbTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var array 
     */
    private $mongoConfig = array(
        'dsn' => 'mongodb://localhost:27017/test',
        'user' => '',
        'password' => ''
    );
    
    /**
     * @var MongoDb
     */
    protected $module;

    /**
     * @var \MongoDb
     */
    protected $db;
    
    /**
     * @var \MongoCollection
     */
    private $userCollection;

    protected function setUp()
    {
        if (!class_exists('Mongo')) {
            $this->markTestSkipped('Mongo is not installed');
        }

        $mongo = new \Mongo();
        
        $this->module = new MongoDb();
        $this->module->_setConfig($this->mongoConfig);
        $this->module->_initialize();

        $this->db = $mongo->selectDB('test');
        $this->userCollection = $this->db->createCollection('users');
        $this->userCollection->insert(array('id' => 1, 'email' => 'miles@davis.com'));
    }

    protected function tearDown()
    {
        if (!is_null($this->userCollection)) {
            $this->userCollection->drop();
        }
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