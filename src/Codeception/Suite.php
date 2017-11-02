<?php
namespace Codeception;

use Codeception\Test\Descriptor;
use Codeception\Test\Interfaces\Dependent;

class Suite extends \PHPUnit_Framework_TestSuite
{
    protected $modules;
    protected $baseName;

    public function reorderDependencies()
    {
        $tests = [];
        foreach ($this->tests as $test) {
            $tests = array_merge($tests, $this->getDependencies($test));
        }

        $queue = [];
        $hashes = [];
        foreach ($tests as $test) {
            if (in_array(spl_object_hash($test), $hashes)) {
                continue;
            }
            $hashes[] = spl_object_hash($test);
            $queue[] = $test;
        }
        $this->tests = $queue;
    }

    protected function getDependencies($test)
    {
        if (!$test instanceof Dependent) {
            return [$test];
        }
        $forTest = Descriptor::getTestSignature($test);
        $tests = [];
        foreach ($test->getDependencies() as $requiredTestName) {
            $required = $this->findMatchedTest($requiredTestName, $forTest);
            if (!$required) {
                continue;
            }
            $tests = array_merge($tests, $this->getDependencies($required));
        }
        $tests[] = $test;
        return $tests;
    }

    protected function findMatchedTest($testSignature, $forTest)
    {
        foreach ($this->tests as $test) {
            $signature = Descriptor::getTestSignature($test);
            if ($signature === $testSignature) {
                return $test;
            }
        }

        throw new \Exception("Dependent test $testSignature for $forTest not found");
    }

    /**
     * @return mixed
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * @param mixed $modules
     */
    public function setModules($modules)
    {
        $this->modules = $modules;
    }

    /**
     * @return mixed
     */
    public function getBaseName()
    {
        return $this->baseName;
    }

    /**
     * @param mixed $baseName
     */
    public function setBaseName($baseName)
    {
        $this->baseName = $baseName;
    }
}
