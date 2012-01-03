<?php
use \Codeception\Util\Stub as Stub;

class UnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Codeception\Module\Unit
     */
    protected $unit;

    /**
     * @var \Codeception\Scenario
     */
    protected $scenario;

    protected $dummy;

    public function setUp() {
        $this->unit = $unit = new \Codeception\Module\Unit();
        $this->scenario = Stub::make('\Codeception\Scenario', array('test' => Stub::makeEmpty('\Codeception\TestCase', array('runStep' => function($step) use ($unit) {
            $action = $step->getAction();
            $args = $step->getArguments();
            call_user_func_array(array($unit, $action), $args);
        }))));

        $test = Stub::make('\Codeception\TestCase', array('scenario' => $this->scenario));
        $this->unit->_before($test);
        require_once $file = \Codeception\Configuration::dataDir().'DummyClass.php';
        $this->dummy = new DummyClass();

    }

    protected function getProperty($name)
    {
        $rc = new \ReflectionObject($this->unit);
        $rp = $rc->getProperty($name);
        $rp->setAccessible(true);
        return $rp->getValue($this->unit);
    }

    public function testTestMethod() {
        $this->unit->testMethod('User.run');
        $this->assertEquals('User', $this->getProperty('testedClass'));
        $this->assertEquals('run', $this->getProperty('testedMethod'));
        $this->assertFalse($this->getProperty('testedStatic'));

        $this->unit->testMethod('Code::update');
        $this->assertEquals('Code', $this->getProperty('testedClass'));
        $this->assertEquals('update', $this->getProperty('testedMethod'));
        $this->assertTrue($this->getProperty('testedStatic'));
    }
    
    public function testHaveStub() {
        $this->unit->haveStub(Stub::make('DummyClass'));
        $stubs = $this->getProperty('stubs');
        $this->assertInstanceOf('DummyClass', $stubs[0]);
    }

}
