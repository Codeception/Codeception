<?php

class CestTest extends \PHPUnit_Framework_TestCase
{
    protected $dispatcher;
    protected $testcase;

    public function setUp() {
        $this->dispatcher = new Symfony\Component\EventDispatcher\EventDispatcher;		
        $conf = \Codeception\Configuration::config();
		require_once \Codeception\Configuration::dataDir().'DummyClass.php';
        $file = \Codeception\Configuration::dataDir().'SimpleCest.php';
        require_once $file;
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
