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
        $this->suiteman = Stub::make('\Codeception\SuiteManager', array(
                'dispatcher' => $this->dispatcher,
                'groupManager' => new \Codeception\Lib\GroupManager([]),
                'suite' => new PHPUnit_Framework_TestSuite(), 
                'settings' => array(
                    'bootstrap' => false, 
                    'class_name' => 'CodeGuy',
                    'namespace' => '',
        )));
    }

    /**
     * @group core
     */
    public function testRun() {
        $events = array();
        $this->dispatcher->addListener('suite.before', function ($e) use (&$events) { $events[] = $e->getName(); });
        $this->dispatcher->addListener('suite.after', function ($e) use (&$events) { $events[] = $e->getName(); });
        $runner = new \Codeception\PHPUnit\Runner;
        $runner->setPrinter(new PHPUnit_TextUI_ResultPrinter($this->dispatcher));
        $this->suiteman->run($runner, new \PHPUnit_Framework_TestResult, array('colors' => false, 'steps' => true, 'debug' => false));
        $this->assertEquals($events, array('suite.before', 'suite.after'));
    }

    /**
     * @group core
     */
    public function testAddCest() {
        $file = \Codeception\Configuration::dataDir().'SimpleCest.php';
        $this->suiteman->addCest($file);
        $this->assertEquals(2, $this->suiteman->getSuite()->count());

        $file = \Codeception\Configuration::dataDir().'SimpleWithNoClassCest.php';
        $this->suiteman->addCest($file);
        $this->assertEquals(3, $this->suiteman->getSuite()->count());
    }

    /**
     * @group core
     */
    public function testAddCept() {
        $file = $file = \Codeception\Configuration::dataDir().'SimpleCept.php';
        $this->suiteman->addCept($file);
        $this->assertEquals(1, $this->suiteman->getSuite()->count());
    }

    /**
     * @group core
     */
    public function testAddTest() {
        $file = $file = \Codeception\Configuration::dataDir().'SimpleTest.php';
        $this->suiteman->addTest($file);
        $this->assertEquals(1, $this->suiteman->getSuite()->count());
    }

    /**
     * When running multiple environments, getClassesFromFile() method in SuiteManager is called once for each env.
     * See \Codeception\Codecept::runSuite() - for each env new SuiteManager is created and tests loaded.
     * Make sure that calling getClassesFromFile() multiple times will always return the same classes.
     *
     * @group core
     */
    public function testAddCestWithEnv() {
        $file = \Codeception\Configuration::dataDir().'SimpleCestWithNamespace.php';
        $this->suiteman->addCest($file);
        $this->assertEquals(3, $this->suiteman->getSuite()->count());
        $newSuiteMan = Stub::make(
            '\Codeception\SuiteManager',
            array(
                 'dispatcher' => $this->dispatcher,
                 'groupManager' => new \Codeception\Lib\GroupManager([]),
                 'suite'      => new PHPUnit_Framework_TestSuite(),
                 'settings'   => array(
                     'bootstrap'  => false,
                     'class_name' => 'CodeGuy',
                     'namespace'  => '',
                 )
            )
        );
        $newSuiteMan->addCest($file);
        $this->assertEquals(3, $newSuiteMan->getSuite()->count());
    }
}
