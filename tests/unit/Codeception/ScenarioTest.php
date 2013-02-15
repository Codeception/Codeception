<?php
use \Codeception\Util\Stub;

class ScenarioTest extends Codeception\TestCase\Test {
    protected $test;

    /**
     * @var \Codeception\Scenario
     */
    protected $testedScenario;

    public function setUp()
    {
        $this->test = new \Codeception\TestCase\Cept($this->dispatcher, array(
            'name' => 'dummy',
            'file' => \Codeception\Configuration::dataDir().'SimpleCept.php'
        ));
        $this->testedScenario = $this->test->getScenario();
        $this->dispatcher->dispatch('test.before', new \Codeception\Event\Test($this));
    }

    public function testCanBeSkipped()
    {
        $this->testedScenario->skip();
        $this->setExpectedException('PHPUnit_Framework_SkippedTestError');
        $this->testedScenario->runStep();
    }

    public function testCanBeSkippedAndDisplayedMessage()
    {
        $this->testedScenario->skip('postpone to release');
        $this->setExpectedException('PHPUnit_Framework_SkippedTestError','postpone to release');
        $this->testedScenario->runStep();
    }

    public function testCanBeIncomplete()
    {
        $this->testedScenario->incomplete();
        $this->setExpectedException('PHPUnit_Framework_IncompleteTestError');
        $this->testedScenario->runStep();
    }

    public function testCanBeIncompleteAndDisplayedMessage()
    {
        $this->testedScenario->incomplete('not tests all parts');
        $this->setExpectedException('PHPUnit_Framework_IncompleteTestError','not tests all parts');
        $this->testedScenario->runStep();
    }

}
