<?php
class TestLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Codeception\TestLoader
     */
    protected $testLoader;

    protected function setUp()
    {
        $this->testLoader = new \Codeception\TestLoader(\Codeception\Configuration::dataDir());
    }

    /**
     * @group core
     */
    public function testAddCept() {
        $this->testLoader->loadTest('SimpleCept.php');
        $this->assertEquals(1, count($this->testLoader->getTests()));
    }

    public function testAddTest()
    {
        $this->testLoader->loadTest('SimpleTest.php');
        $this->assertEquals(1, count($this->testLoader->getTests()));

    }

    public function testAddCeptAbsolutePath()
    {
        $this->testLoader->loadTest(codecept_data_dir('SimpleCept.php'));
        $this->assertEquals(1, count($this->testLoader->getTests()));
    }

    /**
     * @group core
     */
    public function testLoadFileWithFewCases()
    {
        $this->testLoader->loadTest('SimpleNamespacedTest.php');
        $this->assertEquals(3, count($this->testLoader->getTests()));
    }

    /**
     * @group core
     */
    public function testLoadAllTests()
    {
        $this->testLoader = new \Codeception\TestLoader(codecept_data_dir().'claypit/tests');
        $this->testLoader->loadTests();

        $testNames = $this->getTestNames($this->testLoader->getTests());

        $this->assertContainsTestName('order/AnotherCept', $testNames);
        $this->assertContainsTestName('MageGuildCest::darkPower', $testNames);
        $this->assertContainsTestName('FailingTest::testMe', $testNames);
    }

    protected  function getTestNames($tests)
    {
        $testNames = [];
        foreach ($tests as $test) {
            if ($test instanceof \PHPUnit_Framework_TestCase) {
                $testNames[] = \Codeception\TestCase::getTestSignature($test);
            }
        }
        return $testNames;
    }

    protected function assertContainsTestName($name, $testNames)
    {
        $this->assertNotSame(false, array_search($name, $testNames), "$name not found in tests");
    }

    public function testDependencyResolution()
    {
        $this->testLoader->loadTest('SimpleWithDependencyInjectionCest.php');
        $this->assertEquals(3, count($this->testLoader->getTests()));
    }

    protected function shouldFail($msg = '')
    {
        $this->setExpectedException('Exception', $msg);
    }

    public function testFailDependenciesCyclic()
    {
        $this->shouldFail('Failed to resolve cyclic dependencies for class \'FailDependenciesCyclic\IncorrectDependenciesClass\'');
        $this->testLoader->loadTest('FailDependenciesCyclicCest.php');
    }

    public function testFailDependenciesInChain()
    {
        $this->shouldFail('Failed to resolve dependency \'FailDependenciesInChain\AnotherClass\' '
            .'for class \'FailDependenciesInChain\IncorrectDependenciesClass\'');
        $this->testLoader->loadTest('FailDependenciesInChainCest.php');
    }

    public function testFailDependenciesNonExistent()
    {
        $this->shouldFail('Failed to resolve dependencies for class \'FailDependenciesNonExistent\IncorrectDependenciesClass\'. '
            .'Class FailDependenciesNonExistent\NonExistentClass does not exist');
        $this->testLoader->loadTest('FailDependenciesNonExistentCest.php');
    }

    public function testFailDependenciesPrimitiveParam()
    {
        $this->shouldFail('Failed to resolve dependencies for class \'FailDependenciesPrimitiveParam\AnotherClass\'. '
            .'Parameter \'required\' must have default value');
        $this->testLoader->loadTest('FailDependenciesPrimitiveParamCest.php');
    }

}
