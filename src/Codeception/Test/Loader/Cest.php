<?php
namespace Codeception\Test\Loader;

use Codeception\Lib\ExampleSuite;
use Codeception\Lib\Parser;
use Codeception\Test\Cest as CestFormat;
use Codeception\Util\Annotation;

class Cest implements LoaderInterface
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

    public function loadTests($file)
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
                if (strpos($method, '_') === 0) {
                    continue;
                }

                $examples = Annotation::forMethod($unit, $method)->fetchAll('example');
                if (count($examples)) {
                    $examples = array_map(
                        function ($v) {
                            return Annotation::arrayValue($v);
                        }, $examples
                    );
                    $dataProvider = new \PHPUnit_Framework_TestSuite_DataProvider();
                    foreach ($examples as $example) {
                        $test = new CestFormat($unit, $method, $file);
                        $test->getMetadata()->setCurrent(['example' => $example]);
                        $dataProvider->addTest($test);
                    }
                    $this->tests[] = $dataProvider;
                    continue;
                }
                $this->tests[] = new CestFormat($unit, $method, $file);
            }
        }
    }

}