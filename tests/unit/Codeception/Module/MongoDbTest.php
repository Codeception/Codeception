<?php

use Codeception\Module\MongoDb;

class MongoDbTest extends \PHPUnit_Framework_TestCase
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

        $mongo = new \MongoClient();
        
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

    public function testHaveAndSeeInCollection()
    {
        $this->module->haveInCollection('users', array('name' => 'John', 'email' => 'john@coltrane.com'));
        $this->module->seeInCollection('users', array('name' => 'John', 'email' => 'john@coltrane.com'));
    }

    public function testGrabFromCollection()
    {
        $user = $this->module->grabFromCollection('users', array('id' => 1));
        $this->assertTrue(isset($user['email']));
        $this->assertEquals('miles@davis.com',$user['email']);
    }

    public function testSeeNumElementsInCollection()
    {
        $this->module->seeNumElementsInCollection('users', 1);
        $this->module->seeNumElementsInCollection('users', 1, array('email' => 'miles@davis.com'));
        $this->module->seeNumElementsInCollection('users', 0, array('name' => 'Doe'));
    }

    public function testGrabCollectionCount()
    {
        $this->userCollection->insert(array('id' => 2, 'email' => 'louis@armstrong.com'));
        $this->userCollection->insert(array('id' => 3, 'email' => 'dizzy@gillespie.com'));

        $this->assertEquals(1, $this->module->grabCollectionCount('users', array('id' => 3)));
        $this->assertEquals(3, $this->module->grabCollectionCount('users'));
    }

    public function testSeeElementIsArray()
    {
        $this->userCollection->insert(array('id' => 4, 'trumpets' => array('piccolo', 'bass', 'slide')));

        $this->module->seeElementIsArray('users', array('id' => 4), 'trumpets');
    }


    public function testSeeElementIsArrayThrowsError()
    {
        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException');

        $this->userCollection->insert(array('id' => 5, 'trumpets' => array('piccolo', 'bass', 'slide')));
        $this->userCollection->insert(array('id' => 6, 'trumpets' => array('piccolo', 'bass', 'slide')));
        $this->module->seeElementIsArray('users', array(), 'trumpets');
    }

    public function testSeeElementIsObject()
    {
        $trumpet = new \StdClass;

        $trumpet->name = 'Trumpet 1';
        $trumpet->pitch = 'B♭';
        $trumpet->price = array('min' => 458, 'max' => 891);

        $this->userCollection->insert(array('id' => 6, 'trumpet' => $trumpet));

        $this->module->seeElementIsObject('users', array('id' => 6), 'trumpet');
    }

    public function testSeeElementIsObjectThrowsError()
    {
        $trumpet = new \StdClass;

        $trumpet->name = 'Trumpet 1';
        $trumpet->pitch = 'B♭';
        $trumpet->price = array('min' => 458, 'max' => 891);

        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException');

        $this->userCollection->insert(array('id' => 5, 'trumpet' => $trumpet));
        $this->userCollection->insert(array('id' => 6, 'trumpet' => $trumpet));

        $this->module->seeElementIsObject('users', array(), 'trumpet');
    }
}