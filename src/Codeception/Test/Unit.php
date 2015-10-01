<?php
namespace Codeception\Test;

use Codeception\Configuration;
use Codeception\Scenario;
use Codeception\TestInterface;

/**
 * Represents tests from PHPUnit compatible format.
 */
class Unit extends \PHPUnit_Framework_TestCase implements
    Interfaces\Descriptive,
    Interfaces\Reported,
    Interfaces\Dependent,
    TestInterface
{

    /**
     * @var Metadata
     */
    protected $metadata;

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
                return;
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

    public function getFeature()
    {
        $text = $this->getName();
        $text = preg_replace('/([A-Z]+)([A-Z][a-z])/', '\\1 \\2', $text);
        $text = preg_replace('/([a-z\d])([A-Z])/', '\\1 \\2', $text);
        return strtolower($text);
    }

    public function getSignature()
    {
        return get_class($this) . ':' . $this->getName(false);
    }

    public function getFileName()
    {
        return (new \ReflectionClass($this))->getFileName();
    }

    /**
     * @param $module
     *
     * @return \Codeception\Module
     * @throws \Codeception\Exception\TestRuntimeException
     */
    public function getModule($module)
    {
        return $this->getMetadata()->getCurrent('modules')->getModule($module);
    }

    /**
     * @return array
     */
    public function getReportFields()
    {
        return [
            'name'    => $this->getName(),
            'class'   => get_class($this),
            'file'    => $this->getFileName(),
            'feature' => $this->getFeature()
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
        return true;
    }
}
