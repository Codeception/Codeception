<?php
namespace Codeception\Test;

use Codeception\Test\Interfaces\Descriptive;
use Codeception\Test\Interfaces\Plain;
use Codeception\Util\ReflectionHelper;

class Descriptor
{
    /**
     * Provides a test name which can be located by
     *
     * @param \PHPUnit_Framework_SelfDescribing $testCase
     * @return string
     */
    public static function getTestSignature(\PHPUnit_Framework_SelfDescribing $testCase)
    {
        if ($testCase instanceof Descriptive) {
            return $testCase->getSignature();
        }
        if ($testCase instanceof \PHPUnit_Framework_TestCase) {
            return get_class($testCase) . ':' . $testCase->getName(false);
        }
        return $testCase->toString();
    }

    public static function getTestAsString(\PHPUnit_Framework_SelfDescribing $testCase)
    {
        if ($testCase instanceof Descriptive) {
            return $testCase->toString();
        }

        if ($testCase instanceof \PHPUnit_Framework_TestCase) {
            $text = $testCase->getName();
            $text = preg_replace('/([A-Z]+)([A-Z][a-z])/', '\\1 \\2', $text);
            $text = preg_replace('/([a-z\d])([A-Z])/', '\\1 \\2', $text);
            $text = preg_replace('/^test /', '', $text);
            $text = ucfirst(strtolower($text));
            $text = str_replace(['::', 'with data set'], [':', '|'], $text);
            return ReflectionHelper::getClassShortName($testCase) . ': ' . $text;
        }
        return $testCase->toString();
    }

    /**
     * Provides a test file name relative to Codeception root
     *
     * @param \PHPUnit_Framework_SelfDescribing $testCase
     * @return mixed
     */
    public static function getTestFileName(\PHPUnit_Framework_SelfDescribing $testCase)
    {
        if ($testCase instanceof Descriptive) {
            return $testCase->getFileName();
        }
        return (new \ReflectionClass($testCase))->getFileName();
    }

    /**
     * @param \PHPUnit_Framework_SelfDescribing $testCase
     * @return mixed|string
     */
    public static function getTestFullName(\PHPUnit_Framework_SelfDescribing $testCase)
    {
        if ($testCase instanceof Plain) {
            return self::getTestFileName($testCase);
        }
        if ($testCase instanceof Descriptive) {
            $signature = $testCase->getSignature(); // cut everything before ":" from signature
            return self::getTestFileName($testCase) . ':' . preg_replace('~^(.*?):~', '', $signature);
        }
        if ($testCase instanceof \PHPUnit_Framework_TestCase) {
            return self::getTestFileName($testCase) . ':' . $testCase->getName(false);
        }
        return self::getTestFileName($testCase) . ':' . $testCase->toString();
    }
}
