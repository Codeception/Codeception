<?php

use \Codeception\Util\Stub as Stub;

class SuiteManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Codeception\SuiteManager
     */
    protected $suiteman;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $dispatcher;

    public function setUp() {
        $this->dispatcher = new Symfony\Component\EventDispatcher\EventDispatcher;
        $this->suiteman = Stub::make('\Codeception\SuiteManager', array('dispatcher' => $this->dispatcher,'suite' => new PHPUnit_Framework_TestSuite(), 'settings' => array('bootstrap' => false)));
    }

    public function testRun() {
        $events = array();
        $this->dispatcher->addListener('suite.before', function ($e) use (&$events) { $events[] = $e->getName(); });
        $this->dispatcher->addListener('suite.after', function ($e) use (&$events) { $events[] = $e->getName(); });
        $runner = $this->suiteman->run(new PHPUnit_Framework_TestResult(), array('colors' => false, 'steps' => true, 'debug' => false));
        $this->assertEquals($events, array('suite.before', 'suite.after'));
        $this->assertInstanceOf('\Codeception\PHPUnit\Runner', $runner);
    }

    public function testAddCest() {
        $file = \Codeception\Configuration::dataDir().'SimpleCest.php';
        $this->suiteman->addCest($file);
        $this->assertEquals(2, $this->suiteman->getSuite()->count());
    }

    public function testAddCept() {
        $file = $file = \Codeception\Configuration::dataDir().'SimpleCept.php';
        $this->suiteman->addCept($file);
        $this->assertEquals(1, $this->suiteman->getSuite()->count());
    }

}
