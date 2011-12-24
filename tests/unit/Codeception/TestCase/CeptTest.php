<?php

class CeptTest extends \PHPUnit_Framework_TestCase
{
    protected $testcase;

    public function setUp() {
        $this->dispatcher = new Symfony\Component\EventDispatcher\EventDispatcher;
    }

    public function testTestCodecept() {
        $this->testcase = $this->getMock('\Codeception\TestCase\Cept', array('runStep'), array($this->dispatcher, array('name' => 'mocked test', 'file' => 'mocked file')));
        $scenario = $this->testcase->getScenario();
        $scenario->assertion(array('seeEquals',5,5));
        $steps = $scenario->getSteps();
        $step = end($steps);
        $this->testcase->expects($this->once())->method('runStep')->with($this->equalTo($step));
        $this->testcase->testCodecept();
    }

}
