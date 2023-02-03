<?php

declare(strict_types=1);

namespace Codeception\Test;

use Codeception\Test\Interfaces\Descriptive;
use Codeception\Test\Interfaces\Plain;
use Codeception\TestInterface;
use PHPUnit\Framework\SelfDescribing;

use function codecept_relative_path;
use function json_encode;
use function method_exists;
use function preg_replace;
use function realpath;
use function sha1;
use function str_replace;
use function strtolower;
use function substr;
use function ucfirst;

class Descriptor
{
    /**
     * Provides a test name which can be located by
     */
    public static function getTestSignature(Descriptive $test): string
    {
        return $test->getSignature();
    }

    /**
     * Provides a test name which is unique for individual iterations of tests using examples
     */
    public static function getTestSignatureUnique(SelfDescribing $testCase): string
    {
        $env     = '';
        $example = '';

        if (
            method_exists($testCase, 'getScenario')
            && !empty($testCase->getScenario()?->current('env'))
        ) {
            $env = ':' . $testCase->getScenario()->current('env');
        }

        if (
            method_exists($testCase, 'getMetaData')
            && !empty($testCase->getMetadata()->getCurrent('example'))
        ) {
            $currentExample = json_encode($testCase->getMetadata()->getCurrent('example'), JSON_THROW_ON_ERROR);
            $example = ':' . substr(sha1($currentExample), 0, 7);
        }

        return self::getTestSignature($testCase) . $env . $example;
    }

    public static function getTestAsString(SelfDescribing $testCase): string
    {
        return $testCase->toString();
    }

    public static function getTestCaseNameAsString(string $testCaseName): string
    {
        $text = $testCaseName;
        $text = preg_replace('#([A-Z]+)([A-Z][a-z])#', '\\1 \\2', $text);
        $text = preg_replace('#([a-z\d])([A-Z])#', '\\1 \\2', $text);
        $text = preg_replace('#^test #', '', $text);
        $text = ucfirst(strtolower($text));
        return str_replace(['::', 'with data set'], [':', '|'], $text);
    }

    /**
     * Provides a test file name relative to Codeception root
     */
    public static function getTestFileName(Descriptive $test): string
    {
        return codecept_relative_path(realpath($test->getFileName()));
    }

    public static function getTestFullName(Plain|Descriptive $test): string
    {
        if ($test instanceof Plain) {
            return self::getTestFileName($test);
        }

        $signature = $test->getSignature(); // cut everything before ":" from signature
        return self::getTestFileName($test) . ':' . preg_replace('#^(.*?):#', '', $signature);
    }

    /**
     * Provides a test data set index
     */
    public static function getTestDataSetIndex(SelfDescribing $testCase): string
    {
        if ($testCase instanceof TestInterface) {
            $index = $testCase->getMetadata()->getIndex();
            if ($index === null) {
                return '';
            }
            if (is_int($index)) {
                return ' with data set #' . $index;
            }
            return ' with data set "' . $index . '"';
        }
        return '';
    }
}
