<?php

declare(strict_types=1);

namespace Codeception;

use Codeception\PHPUnit\FilterTest;
use PHPUnit\Framework\TestResult;
use PHPUnit\Runner\Filter\ExcludeGroupFilterIterator;
use PHPUnit\Runner\Filter\Factory;
use PHPUnit\Runner\Filter\IncludeGroupFilterIterator;
use ReflectionProperty;

class TestRunner
{
    protected array $config = [];

    public function __construct()
    {
        $this->config = Configuration::config();
    }

    public function prepareSuite(\PHPUnit\Framework\Test $suite, array $arguments)
    {
        $filterAdded = false;

        $filterFactory = new Factory();
        if (!empty($arguments['groups'])) {
            $filterAdded = true;
            $this->addFilterToFactory(
                $filterFactory,
                IncludeGroupFilterIterator::class,
                $arguments['groups']
            );
        }

        if (!empty($arguments['excludeGroups'])) {
            $filterAdded = true;
            $this->addFilterToFactory(
                $filterFactory,
                ExcludeGroupFilterIterator::class,
                $arguments['excludeGroups']
            );
        }

        if (!empty($arguments['filter'])) {
            $filterAdded = true;
            $this->addFilterToFactory(
                $filterFactory,
                FilterTest::class,
                $arguments['filter']
            );
        }

        if ($filterAdded) {
            $suite->injectFilter($filterFactory);
        }
    }

    private function addFilterToFactory(Factory $filterFactory, string $filterClass, $filterParameter)
    {
        $filterReflectionClass = new \ReflectionClass($filterClass);

        $property = new ReflectionProperty(get_class($filterFactory), 'filters');
        $property->setAccessible(true);

        $filters = $property->getValue($filterFactory);
        $filters []= [
            $filterReflectionClass,
            $filterParameter,
        ];
        $property->setValue($filterFactory, $filters);
        $property->setAccessible(false);
    }

    public function doEnhancedRun(
        Suite $suite,
        TestResult $result,
        array $arguments = []
    ) {
        unset($GLOBALS['app']); // hook for not to serialize globals

        $suite->run($result);
        unset($suite);

        return $result;
    }
}
