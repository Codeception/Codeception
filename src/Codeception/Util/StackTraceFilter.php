<?php

declare(strict_types=1);

namespace Codeception\Util;

use PHPUnit\Framework\ExceptionWrapper as PHPUnitExceptionWrapper;
use Throwable;

class StackTraceFilter
{
    protected static array $filteredClassesPattern = [
        'Symfony\Component\Console',
        'Codeception\Command\\',
        'Codeception\TestCase\\',
    ];

    public static function getFilteredStackTrace(Throwable $e, bool $asString = true, bool $filter = true): array|string
    {
        $stackTrace = $asString ? '' : [];

        $trace = $e->getPrevious() ? $e->getPrevious()->getTrace() : $e->getTrace();
        if ($e instanceof PHPUnitExceptionWrapper) {
            $trace = $e->getSerializableTrace();
        }

        $eFile = $e->getFile();
        $eLine = $e->getLine();

        if (!self::frameExists($trace, $eFile, $eLine)) {
            array_unshift(
                $trace,
                ['file' => $eFile, 'line' => $eLine]
            );
        }

        foreach ($trace as $step) {
            if (self::classIsFiltered($step) and $filter) {
                continue;
            }
            if (self::fileIsFiltered($step) and $filter) {
                continue;
            }

            if (!$asString) {
                $stackTrace[] = $step;
                continue;
            }
            if (!isset($step['file'])) {
                continue;
            }

            $stackTrace .= $step['file'] . ':' . $step['line'] . "\n";
        }

        return $stackTrace;
    }

    protected static function classIsFiltered(array $step): bool
    {
        if (!isset($step['class'])) {
            return false;
        }
        $className = $step['class'];

        foreach (self::$filteredClassesPattern as $filteredClassName) {
            if (str_starts_with($className, $filteredClassName)) {
                return true;
            }
        }
        return false;
    }

    protected static function fileIsFiltered(array $step): bool
    {
        if (!isset($step['file'])) {
            return false;
        }

        if (str_contains($step['file'], 'codecept.phar/')) {
            return true;
        }

        if (str_contains($step['file'], 'vendor' . DIRECTORY_SEPARATOR . 'phpunit')) {
            return true;
        }

        if (str_contains($step['file'], 'vendor' . DIRECTORY_SEPARATOR . 'codeception')) {
            return true;
        }

        $modulePath = 'src' . DIRECTORY_SEPARATOR . 'Codeception' . DIRECTORY_SEPARATOR . 'Module';
        if (str_contains($step['file'], $modulePath)) {
            return false; // don`t filter modules
        }

        if (str_contains($step['file'], 'src' . DIRECTORY_SEPARATOR . 'Codeception' . DIRECTORY_SEPARATOR)) {
            return true;
        }

        return false;
    }

    private static function frameExists(array $trace, string $file, int $line): bool
    {
        foreach ($trace as $frame) {
            if (isset($frame['file']) && $frame['file'] == $file &&
                isset($frame['line']) && $frame['line'] == $line) {
                return true;
            }
        }

        return false;
    }
}