<?php

declare(strict_types=1);

use Codeception\Attribute\Group;
use Codeception\Test\Descriptor;
use Codeception\Test\Loader as TestLoader;

#[Group('load')]
final class TestLoaderTest extends \Codeception\PHPUnit\TestCase
{
    protected TestLoader $testLoader;

    protected function _setUp()
    {
        $this->testLoader = new TestLoader(['path' => \Codeception\Configuration::dataDir()]);
    }

    #[Group('core')]
    public function testAddCept()
    {
        $this->testLoader->loadTest('SimpleCept.php');
        $this->assertCount(1, $this->testLoader->getTests());
    }

    public function testAddTest()
    {
        $this->testLoader->loadTest('SimpleTest.php');
        $this->assertCount(1, $this->testLoader->getTests());
    }

    public function testAddCeptAbsolutePath()
    {
        $this->testLoader->loadTest(codecept_data_dir('SimpleCept.php'));
        $this->assertCount(1, $this->testLoader->getTests());
    }

    public function testAddCeptWithoutExtension()
    {
        $this->testLoader->loadTest('SimpleCept');
        $this->assertCount(1, $this->testLoader->getTests());
    }

    #[Group('core')]
    public function testLoadFileWithFewCases()
    {
        $this->testLoader->loadTest('SimpleNamespacedTest.php');
        $this->assertCount(3, $this->testLoader->getTests());
    }

    #[Group('core')]
    public function testLoadAllTests()
    {
        // to autoload dependencies
        Codeception\Util\Autoload::addNamespace(
            'Math',
            codecept_data_dir() . 'claypit/tests/_support/Math'
        );
        Codeception\Util\Autoload::addNamespace(\Codeception\Module::class, codecept_data_dir() . 'claypit/tests/_support');

        $this->testLoader = new TestLoader(['path' => codecept_data_dir() . 'claypit/tests']);
        $this->testLoader->loadTests();

        $testNames = $this->getTestNames($this->testLoader->getTests());

        $this->assertContainsTestName('AnotherCept', $testNames);
        $this->assertContainsTestName('MageGuildCest:darkPower', $testNames);
        $this->assertContainsTestName('FailingTest:testMe', $testNames);
        $this->assertContainsTestName('MathCest:testAddition', $testNames);
        $this->assertContainsTestName('MathTest:testAll', $testNames);
    }

    /**
     * @return string[]
     */
    protected function getTestNames($tests): array
    {
        $testNames = [];
        foreach ($tests as $test) {
            $testNames[] = Descriptor::getTestSignature($test);
        }

        return $testNames;
    }

    protected function assertContainsTestName($name, iterable $testNames)
    {
        $this->assertContains($name, $testNames, "{$name} not found in tests");
    }

    public function testDataProviderReturningArray()
    {
        $this->testLoader->loadTest('SimpleWithDataProviderArrayCest.php');
        $tests = $this->testLoader->getTests();
        $this->assertCount(3, $tests);
    }

    public function testDataProviderReturningGenerator()
    {
        $this->testLoader->loadTest('SimpleWithDataProviderYieldGeneratorCest.php');
        $tests = $this->testLoader->getTests();
        $this->assertCount(3, $tests);
    }

    public function testLoadTestWithExamples()
    {
        $this->testLoader->loadTest('SimpleWithExamplesCest.php');
        $tests = $this->testLoader->getTests();
        $this->assertCount(3, $tests);
    }
}
