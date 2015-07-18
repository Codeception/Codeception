<?php

namespace Codeception;

abstract class TestCase extends \PHPUnit_Framework_TestCase implements \PHPUnit_Framework_SelfDescribing
{
    protected $backupGlobalsBlacklist = ['app'];
    public $_hasStarted = false;

    /**
     * scenario stores steps and test settings: groups, environment, etc
     */

    /**
     * @return Scenario
     */
    abstract public function getScenario();
    abstract public function getEnvironment();

    public static function getTestSignature(\PHPUnit_Framework_TestCase $testCase)
    {
        if ($testCase instanceof TestCase\Interfaces\Descriptive) {
            return $testCase->getSignature();
        }
        return get_class($testCase) . '::' . $testCase->getName(false);
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
        if ($testCase instanceof TestCase\Interfaces\Plain) {
            return self::getTestFileName($testCase);
        }
        return self::getTestFileName($testCase) . ':' . $testCase->getName(false);
    }
}
