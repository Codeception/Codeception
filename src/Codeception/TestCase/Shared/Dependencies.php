<?php
namespace Codeception\TestCase\Shared;

trait Dependencies
{
    protected $dependencies;
    protected $dependencyInput = array();

    protected function handleDependencies()
    {
        if (empty($this->dependencies)) {
            return true;
        }

        $passed = $this->getTestResultObject()->passed();
        $passedKeys = array_map(function ($testname) {
                if ($this instanceof \Codeception\TestCase\Cest) {
                    $testname = str_replace('Codeception\TestCase\Cest::', get_class($this->getTestClass()).'::', $testname);
                }
                return preg_replace('~with data set (.*?)~', '', $testname);
            }, array_keys($passed)
        );

        $dependencyInput = [];

        foreach ($this->dependencies as $dependency) {
            if (strpos($dependency, '::') === false) {
                $dependency = str_replace($this->getName(false), $dependency, $this->getSignature());
            }

            if (!in_array($dependency, $passedKeys)) {
                $this->getTestResultObject()->addError($this, new \PHPUnit_Framework_SkippedTestError(sprintf("This test depends on '$dependency' to pass.")), 0);
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

    public function setDependencies(array $dependencies)
    {
        $this->dependencies = $dependencies;
    }


}