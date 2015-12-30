<?php
namespace Codeception\Test\Format;

use Codeception\Configuration;
use Codeception\Scenario;
use Codeception\Test\Interfaces\Descriptive;
use Codeception\Test\Interfaces\Reported;
use Codeception\Test\Metadata;
use Codeception\Testable;

class TestCase extends \PHPUnit_Framework_TestCase implements
    Descriptive,
    Reported,
    Testable
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

    protected function handleDependencies()
    {
        $dependencies = $this->getMetadata()->getDependencies();
        if (empty($dependencies)) {
            return true;
        }

        $passed = $this->getTestResultObject()->passed();
        $passedKeys = array_map(
            function ($testname) {
                return preg_replace('~with data set (.*?)~', '', $testname);
            }, array_keys($passed)
        );

        $dependencyInput = [];

        foreach ($dependencies as $dependency) {
            if (strpos($dependency, ':') === false) {
                $dependency = str_replace($this->getName(), $dependency, $this->getSignature());
            }

            if (!in_array($dependency, $passedKeys)) {
                $this->getTestResultObject()->addError(
                    $this,
                    new \PHPUnit_Framework_SkippedTestError(sprintf("This test depends on '$dependency' to pass.")),
                    0
                );
                return false;
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
