<?php

class CestTest extends \PHPUnit_Framework_TestCase
{
    protected $dispatcher;
    protected $testcase;

    public function setUp() {
        $this->dispatcher = new Symfony\Component\EventDispatcher\EventDispatcher;
        $conf = \Codeception\Configuration::config();
        $file = $conf['paths']['tests'].'/_data/SimpleCest.php';
        $this->testcase = new \Codeception\TestCase\Cest($this->dispatcher, array('name' => '', 'file' => $file,'class' => new SimpleCest(), 'method' => 'helloWorld', 'static' => false, 'signature' => 'DummyClass.helloWorld'));
    }

    /**
     * Scenario can be loaded from Cest file
     */
    public function testLoadScenario() {
        $this->testcase->loadScenario();
        $this->assertCount(3, $this->testcase->getScenario()->getSteps());
    }

}
