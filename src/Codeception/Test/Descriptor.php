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
    public static function getTestSignature(Descriptive $test): string
    {
        return $test->getSignature();
    }

    public static function getTestSignatureUnique(SelfDescribing $testCase): string
    {
        $signature = self::getTestSignature($testCase);
        if (method_exists($testCase, 'getScenario') && $env = $testCase->getScenario()?->current('env')) {
            $signature .= ':' . $env;
        }
        if (method_exists($testCase, 'getMetadata') && $example = $testCase->getMetadata()->getCurrent('example')) {
            $encoded = json_encode($example, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
            $signature .= ':' . substr(sha1($encoded), 0, 7);
        }

        return $signature;
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
        $text = preg_replace('#^test #i', '', $text);
        $text = ucfirst(strtolower($text));

        return str_replace(['::', 'with data set'], [':', '|'], $text);
    }

    public static function getTestFileName(Descriptive $test): string
    {
        return codecept_relative_path(realpath($test->getFileName()));
    }

    public static function getTestFullName(Plain|Descriptive $test): string
    {
        if ($test instanceof Plain) {
            return self::getTestFileName($test);
        }

        return self::getTestFileName($test) . ':' .
            preg_replace('#^(.*?):#', '', $test->getSignature());
    }

    public static function getTestDataSetIndex(SelfDescribing $testCase): string
    {
        if (!$testCase instanceof TestInterface) {
            return '';
        }
        $index = $testCase->getMetadata()->getIndex();

        return match (true) {
            is_int($index)   => ' with data set #' . $index,
            $index !== null  => ' with data set "' . $index . '"',
            default          => '',
        };
    }
}
