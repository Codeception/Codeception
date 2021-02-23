<?php

declare(strict_types=1);

namespace Codeception\Lib;

class DiTest extends \Codeception\Test\Unit
{
    /**
     * @var Di
     */
    protected $di;

    protected function _setUp(): void
    {
        $this->di = new Di();
    }

    protected function injectionShouldFail(string $msg = ''): void
    {
        $this->expectException(\Codeception\Exception\InjectionException::class);
        if ($msg !== '') {
            $this->expectExceptionMessage($msg);
        }
    }

    public function testFailDependenciesCyclic(): void
    {
        require_once codecept_data_dir().'FailDependenciesCyclic.php';
        $this->injectionShouldFail(
            'Failed to resolve cyclic dependencies for class \'FailDependenciesCyclic\IncorrectDependenciesClass\''
        );
        $this->di->instantiate('FailDependenciesCyclic\IncorrectDependenciesClass');
    }

    public function testFailDependenciesInChain(): void
    {
        require_once codecept_data_dir().'FailDependenciesInChain.php';
        $this->injectionShouldFail('Failed to resolve dependency \'FailDependenciesInChain\AnotherClass\'');
        $this->di->instantiate('FailDependenciesInChain\IncorrectDependenciesClass');
    }

    public function testFailDependenciesNonExistent(): void
    {
        require_once codecept_data_dir().'FailDependenciesNonExistent.php';
        if (PHP_MAJOR_VERSION < 8) {
            $expectedExceptionMessage = 'Class FailDependenciesNonExistent\NonExistentClass does not exist';
        } else {
            $expectedExceptionMessage = 'Class "FailDependenciesNonExistent\NonExistentClass" does not exist';
        }
        $this->injectionShouldFail($expectedExceptionMessage);
        $this->di->instantiate('FailDependenciesNonExistent\IncorrectDependenciesClass');
    }

    public function testFailDependenciesPrimitiveParam(): void
    {
        require_once codecept_data_dir().'FailDependenciesPrimitiveParam.php';
        $this->injectionShouldFail("Parameter 'required' must have default value");
        $this->di->instantiate('FailDependenciesPrimitiveParam\IncorrectDependenciesClass');
    }
}
