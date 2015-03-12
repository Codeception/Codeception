<?php 
namespace Codeception\Lib;

class DiTest extends \Codeception\TestCase\Test
{
    /**
     * @var Di
     */
    protected $di;
    
    protected function setUp()
    {
        $this->di = new Di();
    }

    protected function injectionShouldFail($msg = '')
    {
        $this->setExpectedException('Codeception\Exception\InjectionException', $msg);
    }

    public function testFailDependenciesCyclic()
    {
        require_once codecept_data_dir().'FailDependenciesCyclic.php';
        $this->injectionShouldFail('Failed to resolve cyclic dependencies for class \'FailDependenciesCyclic\IncorrectDependenciesClass\'');
        $this->di->instantiate('FailDependenciesCyclic\IncorrectDependenciesClass');
    }

    public function testFailDependenciesInChain()
    {
        require_once codecept_data_dir().'FailDependenciesInChain.php';
        $this->injectionShouldFail('Failed to resolve dependency \'FailDependenciesInChain\AnotherClass\'');
        $this->di->instantiate('FailDependenciesInChain\IncorrectDependenciesClass');
    }

    public function testFailDependenciesNonExistent()
    {
        require_once codecept_data_dir().'FailDependenciesNonExistent.php';
        $this->injectionShouldFail('Class FailDependenciesNonExistent\NonExistentClass does not exist');
        $this->di->instantiate('FailDependenciesNonExistent\IncorrectDependenciesClass');
    }

    public function testFailDependenciesPrimitiveParam()
    {
        require_once codecept_data_dir().'FailDependenciesPrimitiveParam.php';
        $this->injectionShouldFail('Parameter \'required\' must have default value');
        $this->di->instantiate('FailDependenciesPrimitiveParam\IncorrectDependenciesClass');
    }
} 