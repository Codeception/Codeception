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

    public function testMakeEmptyExceptMagicalPropertyReplaced() {
        $dummy = Stub::makeEmptyExcept('DummyClass', 'getCheckMeToo', array('checkMeToo' => 'checked!'));
        $this->assertEquals('checked!', $dummy->getCheckMeToo());
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

    public function testMakeWithMagicalPropertiesReplaced() {
        $dummy = Stub::make('DummyClass', array('checkMeToo' => 'checked!'));
        $this->assertEquals('checked!', $dummy->checkMeToo);
    }

    public function testMakeMethodSimplyReplaced()
    {
        $dummy = Stub::make('DummyClass', array('helloWorld' => 'good bye world'));
        $this->assertMethodReplaced($dummy);
    }

    public function testCopy() {
        $dummy = Stub::copy($this->dummy, array('checkMe' => 'checked!'));
        $this->assertEquals('checked!', $dummy->getCheckMe());
        $dummy = Stub::copy($this->dummy, array('checkMeToo' => 'checked!'));
        $this->assertEquals('checked!', $dummy->getCheckMeToo());
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
        Stub::update($dummy, array('checkMeToo' => 'done'));
        $this->assertEquals('done', $dummy->getCheckMeToo());
    }

    public function testStubsFromObject()
    {
        $dummy = Stub::make(new \DummyClass());
        $this->assertInstanceOf(
            '\PHPUnit_Framework_MockObject_MockObject',
            $dummy
        );
        $dummy = Stub::make(new \DummyOverloadableClass());
        $this->assertTrue(isset($dummy->__mocked));
        $dummy = Stub::makeEmpty(new \DummyClass());
        $this->assertInstanceOf(
            '\PHPUnit_Framework_MockObject_MockObject',
            $dummy
        );
        $dummy = Stub::makeEmpty(new \DummyOverloadableClass());
        $this->assertTrue(isset($dummy->__mocked));
        $dummy = Stub::makeEmptyExcept(new \DummyClass(),'helloWorld');
        $this->assertInstanceOf(
            '\PHPUnit_Framework_MockObject_MockObject',
            $dummy
        );
        $dummy = Stub::makeEmptyExcept(new \DummyOverloadableClass(), 'helloWorld');
        $this->assertTrue(isset($dummy->__mocked));
        $dummy = Stub::construct(new \DummyClass());
        $this->assertInstanceOf(
            '\PHPUnit_Framework_MockObject_MockObject',
            $dummy
        );
        $dummy = Stub::construct(new \DummyOverloadableClass());
        $this->assertTrue(isset($dummy->__mocked));
        $dummy = Stub::constructEmpty(new \DummyClass());
        $this->assertInstanceOf(
            '\PHPUnit_Framework_MockObject_MockObject',
            $dummy
        );
        $dummy = Stub::constructEmpty(new \DummyOverloadableClass());
        $this->assertTrue(isset($dummy->__mocked));
        $dummy = Stub::constructEmptyExcept(new \DummyClass(),'helloWorld');
        $this->assertInstanceOf(
            '\PHPUnit_Framework_MockObject_MockObject',
            $dummy
        );
        $dummy = Stub::constructEmptyExcept(new \DummyOverloadableClass(), 'helloWorld');
        $this->assertTrue(isset($dummy->__mocked));
    }

    protected function assertMethodReplaced($dummy)
    {
        $this->assertTrue(method_exists($dummy,'helloWorld'));
        $this->assertNotEquals($this->dummy->helloWorld(),$dummy->helloWorld());
        $this->assertEquals($dummy->helloWorld(),'good bye world');

    }

    public static function matcherAndFailMessageProvider()
    {
      return array(
        array(Stub::never(),
          "DummyClass::targetMethod() was not expected to be called."
        ),
        array(Stub::atLeastOnce(),
          "Expectation failed for method name is equal to <string:targetMethod> when invoked at least once.\n"
          . 'Expected invocation at least once but it never occured.'
        ),
        array(Stub::once(),
          "Expectation failed for method name is equal to <string:targetMethod> when invoked 1 time(s).\n"
          . 'Method was expected to be called 1 times, actually called 0 times.'
        ),
        array(Stub::exactly(1),
          "Expectation failed for method name is equal to <string:targetMethod> when invoked 3 time(s).\n"
          . 'Method was expected to be called 3 times, actually called 0 times.'
        ),
        array(Stub::exactly(3),
          "Expectation failed for method name is equal to <string:targetMethod> when invoked 3 time(s).\n"
          . 'Method was expected to be called 3 times, actually called 0 times.'
        ),
      );
    }

    /**
     * @dataProvider matcherAndFailMessageProvider
     */
    public function testMockedMethodIsCalledFail($stubMarshaler, $failMessage) {
      $mock = Stub::makeEmptyExcept('DummyClass', 'call', array('targetMethod' => $stubMarshaler), $this);
      $mock->goodByeWorld();

      try {
        if ($this->thereAreNeverMatcher($stubMarshaler))
          $this->thenWeMustCallMethodForException($mock);
        else
          $this->thenWeDontCallAnyMethodForExceptionJustVerify($mock);
      } catch (PHPUnit_Framework_ExpectationFailedException $e) {
        $this->assertSame( $failMessage, $e->getMessage() );
      }

      $this->resetMockObjects();
    }

    private function thenWeMustCallMethodForException($mock) {
      $mock->call();
    }

    private function thenWeDontCallAnyMethodForExceptionJustVerify($mock) {
      $mock->__phpunit_verify();
      $this->fail('Expected exception');
    }

    private function thereAreNeverMatcher($stubMarshaler) {
      $matcher = $stubMarshaler->getMatcher();

      return 0 == $matcher->getInvocationCount();
    }

    private function resetMockObjects()
    {
      $refl = new ReflectionObject($this);
      $refl = $refl->getParentClass();
      $prop = $refl->getProperty('mockObjects');
      $prop->setAccessible(true);
      $prop->setValue($this, array());
    }

    public static function matcherProvider()
    {
      return array(
        array(0, Stub::never()),
        array(1, Stub::once()),
        array(2, Stub::atLeastOnce()),
        array(3, Stub::exactly(3)),
        array(1, Stub::once(function() {return true;}), true),
        array(2, Stub::atLeastOnce(function() {return array();}), array()),
        array(1, Stub::exactly(1, function() {return null;}), null),
        array(1, Stub::exactly(1, function() {return 'hello world!';}), 'hello world!'),
      );

    }

    /**
     * @dataProvider matcherProvider
     */
    public function testMethodMatcherWithMake($count, $matcher, $expected = false)
    {
        $dummy = Stub::make('DummyClass', array('goodByeWorld' => $matcher), $this);

        $this->repeatCall($count, array($dummy, 'goodByeWorld'), $expected);
    }

    /**
     * @dataProvider matcherProvider
     */
    public function testMethodMatcherWithMakeEmpty($count, $matcher)
    {
        $dummy = Stub::makeEmpty('DummyClass', array('goodByeWorld' => $matcher), $this);

        $this->repeatCall($count, array($dummy, 'goodByeWorld'));
    }

    /**
     * @dataProvider matcherProvider
     */
    public function testMethodMatcherWithMakeEmptyExcept($count, $matcher)
    {
        $dummy = Stub::makeEmptyExcept('DummyClass', 'getCheckMe', array('goodByeWorld' => $matcher), $this);

        $this->repeatCall($count, array($dummy, 'goodByeWorld'));
    }

    /**
     * @dataProvider matcherProvider
     */
    public function testMethodMatcherWithConstruct($count, $matcher)
    {
        $dummy = Stub::construct('DummyClass', array(), array('goodByeWorld' => $matcher), $this);

        $this->repeatCall($count, array($dummy, 'goodByeWorld'));
    }

    /**
     * @dataProvider matcherProvider
     */
    public function testMethodMatcherWithConstructEmpty($count, $matcher)
    {
        $dummy = Stub::constructEmpty('DummyClass', array(), array('goodByeWorld' => $matcher), $this);

        $this->repeatCall($count, array($dummy, 'goodByeWorld'));
    }

    /**
     * @dataProvider matcherProvider
     */
    public function testMethodMatcherWithConstructEmptyExcept($count, $matcher)
    {
        $dummy = Stub::constructEmptyExcept('DummyClass', 'getCheckMe', array(), array('goodByeWorld' => $matcher), $this);

        $this->repeatCall($count, array($dummy, 'goodByeWorld'));
    }

    private function repeatCall($count, $callable, $expected = false)
    {
        for ($i = 0; $i < $count; $i++) {
            $actual = call_user_func($callable);
            if ($expected) $this->assertEquals($expected, $actual);
        }
    }

    public function testConsecutive()
    {
        $dummy = Stub::make('DummyClass', array('helloWorld' => Stub::consecutive('david', 'emma', 'sam', 'amy')));

        $this->assertEquals('david', $dummy->helloWorld());
        $this->assertEquals('emma', $dummy->helloWorld());
        $this->assertEquals('sam', $dummy->helloWorld());
        $this->assertEquals('amy', $dummy->helloWorld());

        // Expected null value when no more values
        $this->assertNull($dummy->helloWorld());
    }
    
    public function testStubPrivateProperties()
    {
        $tester = Stub::construct(
            'myClassWithPrivateProperties', 
            ['name' => 'gamma'], 
            [
                 'randomName' => 'chicken',
                 't' => 'ticky2', 
                 'getRandomName' => function () {
                     return "randomstuff";
                 }
            ]
        );
        $this->assertEquals('gamma', $tester->getName());
        $this->assertEquals('randomstuff', $tester->getRandomName());
        $this->assertEquals('ticky2', $tester->getT());
    }
    
    public function testStubMakeEmptyInterface()
    {
        $stub = Stub::makeEmpty('\Countable', ['count' => 5]);
        $this->assertEquals(5, $stub->count());
    }
}

class myClassWithPrivateProperties
{

    private $name;
    private $randomName = "gaia";
    private $t          = "ticky";

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getRandomName()
    {
        return $this->randomName;
    }

    public function getT()
    {
        return $this->t;
    }
}