<?php
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
        $this->suiteman = new \Codeception\SuiteManager($this->dispatcher, 'suite', \Codeception\Configuration::$defaultSuiteSettings);
    }

    /**
     * @group core
     */
    public function testRun() {
        $events = [];
        $this->dispatcher->addListener('suite.before', function ($e) use (&$events) { $events[] = $e->getName(); });
        $this->dispatcher->addListener('suite.after', function ($e) use (&$events) { $events[] = $e->getName(); });
        $runner = new \Codeception\PHPUnit\Runner;
        $runner->setPrinter(new PHPUnit_TextUI_ResultPrinter($this->dispatcher));
        $this->suiteman->run($runner, new \PHPUnit_Framework_TestResult, ['colors' => false, 'steps' => true, 'debug' => false]);
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

    public function testDependencyResolution()
    {
        $this->suiteman->loadTests(codecept_data_dir().'SimpleWithDependencyInjectionCest.php');
        $this->assertEquals(3, $this->suiteman->getSuite()->count());
    }


}