<?php
namespace Codeception\Test\Loader;

use Codeception\Lib\Parser;
use Codeception\Test\Format\Cest as CestFormat;
use Codeception\Util\Annotation;

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
                if ($test) {
                    $this->tests[] = $test;
                }
            }
        }
    }

    protected function createTestForMethod($cestInstance, $methodName, $file)
    {
        if (strpos($methodName, '_') === 0) {
            return null;
        }
        $cest = new CestFormat($cestInstance, $methodName, $file);
        $cest->getMetadata()->setEnv(Annotation::forMethod($cestInstance, $methodName)->fetchAll('env'));
        return $cest;
    }
}