<?php

use Codeception\Module\MongoDb;
use Codeception\Exception\ModuleException;
use Codeception\Test\Unit;

class MongoDbTest extends Unit
{
    /**
     * @var array
     */
    private $mongoConfig = array(
        'dsn' => 'mongodb://localhost:27017/test?connectTimeoutMS=300',
        'dump' => 'tests/data/dumps/mongo.js',
        'populate' => true
    );

    /**
     * @var MongoDb
     */
    protected $module;

    /**
     * @var \MongoDB\Database
     */
    protected $db;

    /**
     * @var \MongoDB\Collection
     */
    private $userCollection;

    protected function setUp()
    {
        if (!class_exists('\MongoDB\Client')) {
            $this->markTestSkipped('MongoDB is not installed');
        }

        $mongo = new \MongoDB\Client();

        $this->module = new MongoDb(make_container());
        $this->module->_setConfig($this->mongoConfig);
        try {
            $this->module->_initialize();
        } catch (ModuleException $e) {
            $this->markTestSkipped($e->getMessage());
        }

        $this->db = $mongo->selectDatabase('test');
        $this->userCollection = $this->db->users;
        $this->userCollection->insertOne(array('id' => 1, 'email' => 'miles@davis.com'));
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
        $this->assertEquals('miles@davis.com', $user['email']);
    }

    public function testSeeNumElementsInCollection()
    {
        $this->module->seeNumElementsInCollection('users', 1);
        $this->module->seeNumElementsInCollection('users', 1, array('email' => 'miles@davis.com'));
        $this->module->seeNumElementsInCollection('users', 0, array('name' => 'Doe'));
    }

    public function testGrabCollectionCount()
    {
        $this->userCollection->insertOne(array('id' => 2, 'email' => 'louis@armstrong.com'));
        $this->userCollection->insertOne(array('id' => 3, 'email' => 'dizzy@gillespie.com'));

        $this->assertEquals(1, $this->module->grabCollectionCount('users', array('id' => 3)));
        $this->assertEquals(3, $this->module->grabCollectionCount('users'));
    }

    public function testSeeElementIsArray()
    {
        $this->userCollection->insertOne(array('id' => 4, 'trumpets' => array('piccolo', 'bass', 'slide')));

        $this->module->seeElementIsArray('users', array('id' => 4), 'trumpets');
    }


    public function testSeeElementIsArrayThrowsError()
    {
        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException');

        $this->userCollection->insertOne(array('id' => 5, 'trumpets' => array('piccolo', 'bass', 'slide')));
        $this->userCollection->insertOne(array('id' => 6, 'trumpets' => array('piccolo', 'bass', 'slide')));
        $this->module->seeElementIsArray('users', array(), 'trumpets');
    }

    public function testSeeElementIsObject()
    {
        $trumpet = new \StdClass;

        $trumpet->name = 'Trumpet 1';
        $trumpet->pitch = 'B♭';
        $trumpet->price = array('min' => 458, 'max' => 891);

        $this->userCollection->insertOne(array('id' => 6, 'trumpet' => $trumpet));

        $this->module->seeElementIsObject('users', array('id' => 6), 'trumpet');
    }

    public function testSeeElementIsObjectThrowsError()
    {
        $trumpet = new \StdClass;

        $trumpet->name = 'Trumpet 1';
        $trumpet->pitch = 'B♭';
        $trumpet->price = array('min' => 458, 'max' => 891);

        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException');

        $this->userCollection->insertOne(array('id' => 5, 'trumpet' => $trumpet));
        $this->userCollection->insertOne(array('id' => 6, 'trumpet' => $trumpet));

        $this->module->seeElementIsObject('users', array(), 'trumpet');
    }

    public function testUseDatabase()
    {
        $this->module->useDatabase('example');
        $this->module->haveInCollection('stuff', array('name' => 'Ashley', 'email' => 'me@ashleyclarke.me'));
        $this->module->seeInCollection('stuff', array('name' => 'Ashley', 'email' => 'me@ashleyclarke.me'));
        $this->module->dontSeeInCollection('users', array('email' => 'miles@davis.com'));
    }

    public function testLoadDump()
    {
        $testRecords = [
            ['name' => 'Michael Jordan', 'position' => 'sg'],
            ['name' => 'Ron Harper','position' => 'pg'],
            ['name' => 'Steve Kerr','position' => 'pg'],
            ['name' => 'Toni Kukoc','position' => 'sf'],
            ['name' => 'Luc Longley','position' => 'c'],
            ['name' => 'Scottie Pippen','position' => 'sf'],
            ['name' => 'Dennis Rodman','position' => 'pf']
        ];

        foreach ($testRecords as $testRecord) {
            $this->module->haveInCollection('96_bulls', $testRecord);
        }
    }
}
