<?php
namespace Codeception\Test\Loader;

use Codeception\Lib\Parser;
use Codeception\Test\Descriptor;
use Codeception\Test\Unit as UnitFormat;
use Codeception\Util\Annotation;

class Unit implements LoaderInterface
{
    protected $tests = [];

    public function getPattern()
    {
        return '~Test\.php$~';
    }

    public function loadTests($path)
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
        if (!\PHPUnit\Framework\TestSuite::isTestMethod($method)) {
            return;
        }
        $test = \PHPUnit\Framework\TestSuite::createTest($class, $method->name);

        if ($test instanceof \PHPUnit\Framework\DataProviderTestSuite) {
            foreach ($test->tests() as $t) {
                $this->enhancePhpunitTest($t);
            }
            return $test;
        }

        $this->enhancePhpunitTest($test);
        return $test;
    }

    protected function enhancePhpunitTest(\PHPUnit\Framework\Test $test)
    {
        $className = get_class($test);
        $methodName = $test->getName(false);
        $dependencies = \PHPUnit\Util\Test::getDependencies($className, $methodName);
        $test->setDependencies($dependencies);
        if ($test instanceof UnitFormat) {
            $test->getMetadata()->setParamsFromAnnotations(Annotation::forMethod($test, $methodName)->raw());
            $test->getMetadata()->setFilename(Descriptor::getTestFileName($test));
        }
    }
}
