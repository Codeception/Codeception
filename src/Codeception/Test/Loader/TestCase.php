<?php
namespace Codeception\Test\Loader;

use Codeception\Lib\Parser;
use Codeception\Test\Format\TestCase as TestCaseFormat;
use Codeception\Util\Annotation;

class TestCase implements Loader
{
    protected $tests = [];

    public function getPattern()
    {
        return '~Test\.php$~';
    }

    function loadTests($path)
    {
        Parser::load($path);
        $testClasses = Parser::getClassesFromFile($path);

        foreach ($testClasses as $testClass) {
            $reflected = new \ReflectionClass($testClass);

            if (!$reflected->isInstantiable()) {
                continue;
            }

            foreach ($reflected->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                $test = $this->createTestFromPhpUnitMethod($reflected, $method);
                if (!$test) {
                    continue;
                }
                $this->tests[] = $test;
            }
        }
    }

    public function getTests()
    {
        return $this->tests;
    }

    protected function createTestFromPhpUnitMethod(\ReflectionClass $class, \ReflectionMethod $method)
    {
        if (!\PHPUnit_Framework_TestSuite::isTestMethod($method)) {
            return;
        }
        $test = \PHPUnit_Framework_TestSuite::createTest($class, $method->name);

        if ($test instanceof \PHPUnit_Framework_TestSuite_DataProvider) {
            foreach ($test->tests() as $t) {
                $this->enhancePhpunitTest($t);
            }
            return $test;
        }

        $this->enhancePhpunitTest($test);
        return $test;
    }

    protected function enhancePhpunitTest(\PHPUnit_Framework_TestCase $test)
    {
        $className = get_class($test);
        $methodName = $test->getName(false);
        $test->setDependencies($deps = \PHPUnit_Util_Test::getDependencies($className, $methodName));
        if ($test instanceof TestCaseFormat) {
            $test->getMetadata()->setDependencies($deps);
            $test->getMetadata()->setEnv(Annotation::forMethod($test, $methodName)->fetchAll('env'));
        }
    }

}