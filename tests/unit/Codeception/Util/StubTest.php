<?php
use \Codeception\Util\Stub as Stub;

class StubTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DummyClass
     */
    protected $dummy;

    public function setUp() {
        $conf = \Codeception\Configuration::config();
        require_once $file = \Codeception\Configuration::dataDir().'DummyClass.php';
        $this->dummy = new DummyClass(true);
    }
    
    public function testMakeEmpty() {
        $dummy = Stub::makeEmpty('DummyClass');
        $this->assertInstanceOf('DummyClass', $dummy);
        $this->assertTrue(method_exists($dummy,'helloWorld'));
        $this->assertNull($dummy->helloWorld());
    }
    
    public function testMakeEmptyMethodReplaced() {
        $dummy = Stub::makeEmpty('DummyClass', array('helloWorld' => function () { return 'good bye world'; }));
        $this->assertTrue(method_exists($dummy,'helloWorld'));
        $this->assertNotEquals($this->dummy->helloWorld(),$dummy->helloWorld());
        $this->assertEquals($dummy->helloWorld(),'good bye world');
    }

    public function testMakeEmptyExcept() {
        $dummy = Stub::makeEmptyExcept('DummyClass', 'helloWorld');
        $this->assertEquals($this->dummy->helloWorld(), $dummy->helloWorld());
        $this->assertNull($dummy->goodByeWorld());
    }

    public function testMakeEmptyExceptProperyRepalced() {
        $dummy = Stub::makeEmptyExcept('DummyClass', 'getCheckMe', array('checkMe' => 'checked!'));
        $this->assertEquals('checked!', $dummy->getCheckMe());
    }
    
    public function testFactory() {
        $dummies = Stub::factory('DummyClass',2);
        $this->assertCount(2, $dummies);
        $this->assertInstanceOf('DummyClass', $dummies[0]);
    }
    
    public function testMake() {
        $dummy = Stub::make('DummyClass', array('goodByeWorld' => function () { return 'hello world'; }));
        $this->assertEquals($this->dummy->helloWorld(), $dummy->helloWorld());
        $this->assertEquals("hello world", $dummy->goodByeWorld());
    }
    
    public function testCopy() {
        $dummy = Stub::copy($this->dummy, array('checkMe' => 'checked!'));
        $this->assertEquals('checked!', $dummy->getCheckMe());
    }

    public function testConstruct()
    {
        $dummy = Stub::construct('DummyClass', array('checkMe' => 'checked!'));
        $this->assertEquals('constructed: checked!', $dummy->getCheckMe());

        $dummy = Stub::construct('DummyClass', array('checkMe' => 'checked!'), array('targetMethod' => function () { return false; }));
        $this->assertEquals('constructed: checked!', $dummy->getCheckMe());
        $this->assertEquals(false, $dummy->targetMethod());
    }

    public function testConstructEmpty()
    {
        $dummy = Stub::constructEmpty('DummyClass', array('checkMe' => 'checked!'));
        $this->assertNull($dummy->getCheckMe());
    }

    public function testConstructEmptyExcept()
    {
        $dummy = Stub::constructEmptyExcept('DummyClass', 'getCheckMe', array('checkMe' => 'checked!'));
        $this->assertNull($dummy->targetMethod());
        $this->assertEquals('constructed: checked!', $dummy->getCheckMe());
    }


}
