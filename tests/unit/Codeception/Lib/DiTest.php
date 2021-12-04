<?php

declare(strict_types=1);

namespace Codeception\Lib;

use Codeception\Exception\InjectionException;

class DiTest extends \Codeception\Test\Unit
{
    /**
     * @var Di
     */
    protected Di $di;

    protected function _setUp()
    {
        $this->di = new Di();
    }

    protected function injectionShouldFail(string $msg = '')
    {
        $this->expectException(InjectionException::class);
        if ($msg !== '') {
            $this->expectExceptionMessage($msg);
        }
    }

    public function testFailDependenciesCyclic()
    {
        require_once codecept_data_dir().'FailDependenciesCyclic.php';
        $this->injectionShouldFail(
            'Failed to resolve cyclic dependencies for class \'FailDependenciesCyclic\IncorrectDependenciesClass\''
        );
        $this->di->instantiate('FailDependenciesCyclic\IncorrectDependenciesClass');
    }

    public function testFailDependenciesInChain()
    {
        require_once codecept_data_dir() . 'FailDependenciesInChain.php';
        $this->injectionShouldFail('Failed to resolve dependency \'FailDependenciesInChain\AnotherClass\'');
        $this->di->instantiate('FailDependenciesInChain\IncorrectDependenciesClass');
    }

    public function testFailDependenciesNonExistent()
    {
        require_once codecept_data_dir() . 'FailDependenciesNonExistent.php';
        if (PHP_MAJOR_VERSION < 8) {
            $expectedExceptionMessage = 'Class FailDependenciesNonExistent\NonExistentClass does not exist';
        } else {
            $expectedExceptionMessage = 'Class "FailDependenciesNonExistent\NonExistentClass" does not exist';
        }

        $this->injectionShouldFail($expectedExceptionMessage);
        $this->di->instantiate('FailDependenciesNonExistent\IncorrectDependenciesClass');
    }

    public function testFailDependenciesPrimitiveParam()
    {
        require_once codecept_data_dir() . 'FailDependenciesPrimitiveParam.php';
        $this->injectionShouldFail("Parameter 'required' must have default value");
        $this->di->instantiate('FailDependenciesPrimitiveParam\IncorrectDependenciesClass');
    }
}
