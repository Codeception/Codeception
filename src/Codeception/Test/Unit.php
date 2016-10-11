<?php
namespace Codeception\Test;

use Codeception\Configuration;
use Codeception\Exception\ModuleException;
use Codeception\Scenario;
use Codeception\TestInterface;

/**
 * Represents tests from PHPUnit compatible format.
 */
class Unit extends \PHPUnit_Framework_TestCase implements
    Interfaces\Reported,
    Interfaces\Dependent,
    TestInterface
{

    /**
     * @var Metadata
     */
    private $metadata;

    public function getMetadata()
    {
        if (!$this->metadata) {
            $this->metadata = new Metadata();
        }
        return $this->metadata;
    }

    protected function setUp()
    {
        if ($this->getMetadata()->isBlocked()) {
            if ($this->getMetadata()->getSkip() !== null) {
                $this->markTestSkipped($this->getMetadata()->getSkip());
            }
            if ($this->getMetadata()->getIncomplete() !== null) {
                $this->markTestIncomplete($this->getMetadata()->getIncomplete());
            }
            return;
        }
        $scenario = new Scenario($this);
        $actorClass = $this->getMetadata()->getCurrent('actor');
        if ($actorClass) {
            $I = new $actorClass($scenario);
            $property = lcfirst(Configuration::config()['actor']);
            $this->$property = $I;
        }
        $this->getMetadata()->getService('di')->injectDependencies($this); // injecting dependencies
        $this->_before();
    }

    /**
     * @Override
     */
    protected function _before()
    {
    }

    protected function tearDown()
    {
        $this->_after();
    }

    /**
     * @Override
     */
    protected function _after()
    {
    }

    /**
     * @param $module
     * @return \Codeception\Module
     * @throws ModuleException
     */
    public function getModule($module)
    {
        $modules = $this->getMetadata()->getCurrent('modules');
        if (!isset($modules[$module])) {
            throw new ModuleException($module, "Module can't be accessed");
        }
        return $modules[$module];
    }

    /**
     * Returns current values
     */
    public function getCurrent($current)
    {
        return $this->getMetadata()->getCurrent($current);
    }

    /**
     * @return array
     */
    public function getReportFields()
    {
        return [
            'name'    => $this->getName(),
            'class'   => get_class($this),
            'file'    => $this->getMetadata()->getFilename()
        ];
    }

    public function getDependencies()
    {
        $names = [];
        foreach ($this->getMetadata()->getDependencies() as $required) {
            if ((strpos($required, ':') === false) and method_exists($this, $required)) {
                $required = get_class($this) . ":$required";
            }
            $names[] = $required;
        }
        return $names;
    }

    /**
     * Reset PHPUnit's dependencies
     * @return bool
     */
    public function handleDependencies()
    {
        $dependencies = $this->getDependencies();
        if (empty($dependencies)) {
            return true;
        }
        $passed = $this->getTestResultObject()->passed();
        $dependencyInput = [];

        foreach ($dependencies as $dependency) {
            $dependency = str_replace(':', '::', $dependency); // Codeception => PHPUnit format
            if (strpos($dependency, '::') === false) {         // check it is method of same class
                $dependency = get_class($this) . '::' . $dependency;
            }
            if (isset($passed[$dependency])) {
                $dependencyInput[] = $passed[$dependency]['result'];
            } else {
                $dependencyInput[] = null;
            }
        }
        $this->setDependencyInput($dependencyInput);
        return true;
    }
}
