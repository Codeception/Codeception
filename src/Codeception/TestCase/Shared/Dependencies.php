<?php
namespace Codeception\TestCase\Shared;

trait Dependencies
{
    protected $dependencies;

    protected function handleDependencies()
    {
        if (empty($this->dependencies)) {
            return true;
        }

        $passed = $this->getTestResultObject()->passed();
        $testNames = array_map(function ($testname) {
                return preg_replace('~with data set (.*?)~', '', $testname);
            }, array_keys($passed)
        );

        $testNames = array_unique($testNames);

        foreach ($this->dependencies as $dependency) {
            if (in_array($dependency, $testNames)) {
                continue;
            }
            $this->getTestResultObject()->addError(
                 $this,
                 new \PHPUnit_Framework_SkippedTestError("This test depends on '$dependency' to pass."),
                 0
            );
            return false;
        }
        return true;
    }

    public function setDependencies(array $dependencies)
    {
        $this->dependencies = $dependencies;
    }


}