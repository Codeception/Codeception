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
        $this->assertMethodReplaced($dummy);
    }

    public function testMakeEmptyMethodSimplyReplaced()
    {
        $dummy = Stub::makeEmpty('DummyClass', array('helloWorld' => 'good bye world'));
        $this->assertMethodReplaced($dummy);
    }

    public function testMakeEmptyExcept() {
        $dummy = Stub::makeEmptyExcept('DummyClass', 'helloWorld');
        $this->assertEquals($this->dummy->helloWorld(), $dummy->helloWorld());
        $this->assertNull($dummy->goodByeWorld());
    }

    public function testMakeEmptyExceptPropertyReplaced() {
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

    public function testMakeMethodReplaced() {
        $dummy = Stub::make('DummyClass', array('helloWorld' => function () { return 'good bye world'; }));
        $this->assertMethodReplaced($dummy);
    }

    public function testMakeMethodSimplyReplaced()
    {
        $dummy = Stub::make('DummyClass', array('helloWorld' => 'good bye world'));
        $this->assertMethodReplaced($dummy);
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

    public function testConstructMethodReplaced() {
        $dummy = Stub::construct('DummyClass', array(), array('helloWorld' => function () { return 'good bye world'; }));
        $this->assertMethodReplaced($dummy);
    }

    public function testConstructMethodSimplyReplaced()
    {
        $dummy = Stub::make('DummyClass', array('helloWorld' => 'good bye world'));
        $this->assertMethodReplaced($dummy);
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

    public function testUpdate()
    {
        $dummy = Stub::construct('DummyClass');
        Stub::update($dummy, array('checkMe' => 'done'));
        $this->assertEquals('done', $dummy->getCheckMe());
    }

    public function testStubsFromObject()
    {
        $dummy = Stub::make(new \DummyClass());
        $this->assertTrue(isset($dummy->__mocked));
        $dummy = Stub::makeEmpty(new \DummyClass());
        $this->assertTrue(isset($dummy->__mocked));
        $dummy = Stub::makeEmptyExcept(new \DummyClass(),'helloWorld');
        $this->assertTrue(isset($dummy->__mocked));
        $dummy = Stub::construct(new \DummyClass());
        $this->assertTrue(isset($dummy->__mocked));
        $dummy = Stub::constructEmpty(new \DummyClass());
        $this->assertTrue(isset($dummy->__mocked));
        $dummy = Stub::constructEmptyExcept(new \DummyClass(),'helloWorld');
        $this->assertTrue(isset($dummy->__mocked));
    }

    protected function assertMethodReplaced($dummy)
    {
        $this->assertTrue(method_exists($dummy,'helloWorld'));
        $this->assertNotEquals($this->dummy->helloWorld(),$dummy->helloWorld());
        $this->assertEquals($dummy->helloWorld(),'good bye world');

    }

}
