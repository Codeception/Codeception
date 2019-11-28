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
     * @param \PHPUnit\Framework\SelfDescribing $testCase
     * @return string
     */
    public static function getTestSignature(\PHPUnit\Framework\SelfDescribing $testCase)
    {
        if ($testCase instanceof Descriptive) {
            return $testCase->getSignature();
        }
        if ($testCase instanceof \PHPUnit\Framework\TestCase) {
            return get_class($testCase) . ':' . $testCase->getName(false);
        }
        return $testCase->toString();
    }

    /**
     * Provides a test name which is unique for individual iterations of tests using examples
     *
     * @param \PHPUnit\Framework\SelfDescribing $testCase
     * @return string
     */
    public static function getTestSignatureUnique(\PHPUnit\Framework\SelfDescribing $testCase)
    {
        $env     = '';
        $example = '';

        if (method_exists($testCase, 'getScenario')
            && !empty($testCase->getScenario()->current('env'))
        ) {
            $env = ':' . $testCase->getScenario()->current('env');
        }

        if (method_exists($testCase, 'getMetaData')
            && !empty($testCase->getMetadata()->getCurrent('example'))
        ) {
            $example = ':' . substr(sha1(json_encode($testCase->getMetadata()->getCurrent('example'))), 0, 7);
        }

        return self::getTestSignature($testCase) . $env . $example;
    }

    public static function getTestAsString(\PHPUnit\Framework\SelfDescribing $testCase)
    {
        if ($testCase instanceof \PHPUnit\Framework\TestCase) {
            $text = self::getTestCaseNameAsString($testCase->getName());
            return ReflectionHelper::getClassShortName($testCase) . ': ' . $text;
        }

        return $testCase->toString();
    }

  /**
   * @param string $testCaseName
   * @return string
   */
    public static function getTestCaseNameAsString($testCaseName)
    {
        $text = $testCaseName;
        $text = preg_replace('/([A-Z]+)([A-Z][a-z])/', '\\1 \\2', $text);
        $text = preg_replace('/([a-z\d])([A-Z])/', '\\1 \\2', $text);
        $text = preg_replace('/^test /', '', $text);
        $text = ucfirst(strtolower($text));
        $text = str_replace(['::', 'with data set'], [':', '|'], $text);
        return $text;
    }

    /**
     * Provides a test file name relative to Codeception root
     *
     * @param \PHPUnit\Framework\SelfDescribing $testCase
     * @return mixed
     */
    public static function getTestFileName(\PHPUnit\Framework\SelfDescribing $testCase)
    {
        if ($testCase instanceof Descriptive) {
            return codecept_relative_path(realpath($testCase->getFileName()));
        }
        return (new \ReflectionClass($testCase))->getFileName();
    }

    /**
     * @param \PHPUnit\Framework\SelfDescribing $testCase
     * @return mixed|string
     */
    public static function getTestFullName(\PHPUnit\Framework\SelfDescribing $testCase)
    {
        if ($testCase instanceof Plain) {
            return self::getTestFileName($testCase);
        }
        if ($testCase instanceof Descriptive) {
            $signature = $testCase->getSignature(); // cut everything before ":" from signature
            return self::getTestFileName($testCase) . ':' . preg_replace('~^(.*?):~', '', $signature);
        }
        if ($testCase instanceof \PHPUnit\Framework\TestCase) {
            return self::getTestFileName($testCase) . ':' . $testCase->getName(false);
        }
        return self::getTestFileName($testCase) . ':' . $testCase->toString();
    }

    /**
     * Provides a test data set index
     *
     * @param \PHPUnit\Framework\SelfDescribing $testCase
     * @return int|null
     */
    public static function getTestDataSetIndex(\PHPUnit\Framework\SelfDescribing $testCase)
    {
        if ($testCase instanceof Descriptive) {
            return $testCase->getMetadata()->getIndex();
        }
        return null;
    }
}
