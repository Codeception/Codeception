<?php

namespace Codeception;

use Codeception\Event\StepEvent;
use Codeception\Exception\ConditionalAssertionFailed;
use Codeception\TestCase\Shared\Dependencies;
use Codeception\TestCase\Test;
use Symfony\Component\EventDispatcher\Event;

abstract class TestCase extends \PHPUnit_Framework_TestCase implements \PHPUnit_Framework_SelfDescribing
{
    protected $backupGlobalsBlacklist = array('app');

    /**
     * scenario stores steps and test settings: groups, environment, etc
     */
    abstract public function getScenario();

    public static function getTestSignature(\PHPUnit_Framework_TestCase $testCase)
    {
        if ($testCase instanceof TestCase\Interfaces\Descriptive) {
            return $testCase->getSignature();
        }
        return get_class($testCase).'::'.$testCase->getName(false);
    }

    public static function getTestFileName(\PHPUnit_Framework_TestCase $testCase)
    {
        if ($testCase instanceof TestCase\Interfaces\Descriptive) {
            return $testCase->getFileName();
        }
        return (new \ReflectionClass($testCase))->getFileName();
    }

    public static function getTestFullName(\PHPUnit_Framework_TestCase $testCase)
    {
<<<<<<< HEAD
        if ($testCase instanceof TestCase\Interfaces\Plain) {
            return self::getTestFileName($testCase);
=======
        if (empty($this->dependencies)) return true;

        $passed = $this->getTestResultObject()->passed();
        $that = $this;
        $testNames = array_map(function($testname) use ($that) {
            if ($that instanceof \Codeception\TestCase\Cest) {
                $testname = str_replace('Codeception\TestCase\Cest::', get_class($that->getTestClass()).'::', $testname);
            }
            return preg_replace('~with data set (.*?)~','', $testname);
        }, array_keys($passed));
        $testNames = array_unique($testNames);

        $dependencyInput = array();
        foreach ($this->dependencies as $dependency) {
            if (strpos($dependency, '::') === FALSE) {
                $className = $this instanceof \Codeception\TestCase\Cest
                    ? get_class($this->getTestClass())
                    : get_class($this);
                $dependency = "$className::$dependency";
            }

            if (!in_array($dependency, $testNames)) {
                $this->getTestResultObject()->addError($this, new \PHPUnit_Framework_SkippedTestError("This test depends on '$dependency' to pass."),0);
                return false;
            }
            if (isset($passed[$dependency])) {
                $dependencyInput[] = $passed[$dependency]['result'];
            } else {
                $dependencyInput[] = NULL;
            }
>>>>>>> c90c799cf588bade076fcee98eff0b3f30839adc
        }
        return self::getTestFileName($testCase).':'.$testCase->getName(false);
    }
}
