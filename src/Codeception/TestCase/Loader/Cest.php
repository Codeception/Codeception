<?php
namespace Codeception\TestCase\Loader;

use Codeception\Lib\Parser;

class Cest implements Loader
{
    protected $tests = [];

    public function getTests()
    {
        return $this->tests;
    }

    public function getPattern()
    {
        return '~Cest\.php$~';
    }

    function loadTests($file)
    {
        Parser::load($file);
        $testClasses = Parser::getClassesFromFile($file);

        foreach ($testClasses as $testClass) {
            if (substr($testClass, -strlen('Cest')) !== 'Cest') {
                continue;
            }
            if (!(new \ReflectionClass($testClass))->isInstantiable()) {
                continue;
            }

            $unit = new $testClass;

            $methods = get_class_methods($testClass);
            foreach ($methods as $method) {
                $test = $this->createTestForMethod($unit, $method, $file);
                if (!$test) {
                    continue;
                }
                $this->tests[] = $test;
            }
        }
    }

    protected function createTestForMethod($cestInstance, $methodName, $file)
    {
        if ((strpos($methodName, '_') === 0) || ($methodName == '__construct')) {
            return null;
        }
        $testClass = get_class($cestInstance);

        $cest = new \Codeception\TestCase\Cest();
        $cest->configName($methodName)
            ->configFile($file)
            ->config('testClassInstance', $cestInstance)
            ->config('testMethod', $methodName);

        $cest->setDependencies(\PHPUnit_Util_Test::getDependencies($testClass, $methodName));
        return $cest;
    }
}