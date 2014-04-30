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
        $this->testLoader->loadTests();
        $this->assertContainsTestName('SimpleCept.php', $this->testLoader->getTests());
        $this->assertContainsTestName('SimpleCest::helloWorld', $this->testLoader->getTests());
        $this->assertContainsTestName('SampleTest::testOfTest', $this->testLoader->getTests());
    }

    protected function assertContainsTestName($name, $tests)
    {
        foreach ($tests as $test) {
            if ($test instanceof \PHPUnit_Framework_TestCase) {
                $testName = \Codeception\TestCase::getTestSignature($test);
                if ($testName == $name) return;
            }
        }
        $this->fail("$name not found in tests");

    }

}