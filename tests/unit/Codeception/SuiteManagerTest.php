<?php
if (!defined('PHPUNIT_TESTSUITE')) define('PHPUNIT_TESTSUITE', true);

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

    /**
     * @var \Codeception\PHPUnit\Runner
     */
    protected $runner;

    public function setUp() {
        $this->dispatcher = new Symfony\Component\EventDispatcher\EventDispatcher;
        $settings = \Codeception\Configuration::$defaultSuiteSettings;
        $settings['class_name'] = 'CodeGuy';
        $this->suiteman = new \Codeception\SuiteManager($this->dispatcher, 'suite', $settings);
        
        $printer = \Codeception\Util\Stub::makeEmpty('PHPUnit_TextUI_ResultPrinter');
        $this->runner = new \Codeception\PHPUnit\Runner;
        $this->runner->setPrinter($printer);
    }

    /**
     * @group core
     */
    public function testRun() {
        $events = [];
        $this->dispatcher->addListener('suite.before', function ($e) use (&$events) { $events[] = $e->getName(); });
        $this->dispatcher->addListener('suite.after', function ($e) use (&$events) { $events[] = $e->getName(); });
        $this->suiteman->run($this->runner, new \PHPUnit_Framework_TestResult, ['colors' => false, 'steps' => true, 'debug' => false]);
        $this->assertEquals($events, ['suite.before', 'suite.after']);
    }

    /**
     * @group core
     */
    public function testFewTests() {
        $file = \Codeception\Configuration::dataDir().'SimpleCest.php';
        $this->suiteman->loadTests($file);
        $this->assertEquals(2, $this->suiteman->getSuite()->count());

        $file = \Codeception\Configuration::dataDir().'SimpleWithNoClassCest.php';
        $this->suiteman->loadTests($file);
        $this->assertEquals(3, $this->suiteman->getSuite()->count());
    }

    /**
     * When running multiple environments, getClassesFromFile() method in SuiteManager is called once for each env.
     * See \Codeception\Codecept::runSuite() - for each env new SuiteManager is created and tests loaded.
     * Make sure that calling getClassesFromFile() multiple times will always return the same classes.
     *
     * @group core
     */
    public function testAddCestWithEnv() {
        $file = \Codeception\Configuration::dataDir().'SimpleNamespacedTest.php';
        $this->suiteman->loadTests($file);
        $this->assertEquals(3, $this->suiteman->getSuite()->count());
        $newSuiteMan = new \Codeception\SuiteManager($this->dispatcher, 'suite', \Codeception\Configuration::$defaultSuiteSettings);
        $newSuiteMan->loadTests($file);
        $this->assertEquals(3, $newSuiteMan->getSuite()->count());
    }

    public function testGroupEventsAreFired()
    {
        $events = [];
        $this->dispatcher->addListener('test.before', function ($e) use (&$events) { $events[] = $e->getName(); });
        $this->dispatcher->addListener('test.before.admin', function ($e) use (&$events) { $events[] = $e->getName(); });
        $this->dispatcher->addListener('test.after', function ($e) use (&$events) { $events[] = $e->getName(); });
        $this->dispatcher->addListener('test.after.admin', function ($e) use (&$events) { $events[] = $e->getName(); });

        $this->suiteman->loadTests(codecept_data_dir().'SimpleAdminGroupCest.php');
        $this->suiteman->run($this->runner, new \PHPUnit_Framework_TestResult, ['silent' => true, 'colors' => false, 'steps' => true, 'debug' => false]);
        $this->assertContains('test.before', $events);
        $this->assertContains('test.before.admin', $events);
        $this->assertContains('test.after.admin', $events);
    }

}