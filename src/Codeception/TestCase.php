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
        return self::getTestFileName($testCase).':'.$testCase->getName(false);
    }
}
