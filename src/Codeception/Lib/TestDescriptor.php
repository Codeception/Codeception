<?php
namespace Codeception\Lib;

use Codeception\TestCase\Interfaces\Descriptive;
use Codeception\TestCase\Interfaces\Plain;

class TestDescriptor
{
    public static function getTestSignature(\PHPUnit_Framework_SelfDescribing $testCase)
    {
        if ($testCase instanceof Descriptive) {
            return $testCase->getSignature();
        }
        if ($testCase instanceof \PHPUnit_Framework_TestCase) {
            return get_class($testCase) . '::' . $testCase->getName(false);
        }
        return $testCase->toString();
    }

    public static function getTestFileName(\PHPUnit_Framework_SelfDescribing $testCase)
    {
        if ($testCase instanceof Descriptive) {
            return $testCase->getFileName();
        }
        return (new \ReflectionClass($testCase))->getFileName();
    }

    public static function getTestFullName(\PHPUnit_Framework_SelfDescribing $testCase)
    {
        if ($testCase instanceof Plain) {
            return self::getTestFileName($testCase);
        }
        if ($testCase instanceof Descriptive) {
            return self::getTestFileName($testCase) . ':' . $testCase->getName();
        }
        if ($testCase instanceof \PHPUnit_Framework_TestCase) {
            return self::getTestFileName($testCase) . ':' . $testCase->getName(false);
        }
        return self::getTestFileName($testCase) . ':' . $testCase->toString();
    }
}