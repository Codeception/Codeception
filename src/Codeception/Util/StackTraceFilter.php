<?php

declare(strict_types=1);

namespace Codeception\Util;

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
        $trace = ($e->getPrevious() ?? $e)->getTrace();

        if (!self::frameExists($trace, $e->getFile(), $e->getLine())) {
            array_unshift($trace, ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }

        if ($filter) {
            $trace = array_values(array_filter(
                $trace,
                static fn(array $step): bool => !self::classIsFiltered($step) && !self::fileIsFiltered($step)
            ));
        }

        if (!$asString) {
            return $trace;
        }

        return implode("\n", array_map(
            static fn(array $step): string => $step['file'] . ':' . $step['line'],
            array_filter($trace, static fn(array $step): bool => isset($step['file'], $step['line']))
        ));
    }

    protected static function classIsFiltered(array $step): bool
    {
        if (!isset($step['class'])) {
            return false;
        }

        foreach (self::$filteredClassesPattern as $pattern) {
            if (str_starts_with($step['class'], $pattern)) {
                return true;
            }
        }
        return false;
    }

    /** @param string[] $step */
    protected static function fileIsFiltered(array $step): bool
    {
        if (!isset($step['file'])) {
            return false;
        }

        $file   = $step['file'];
        $vendor = 'vendor' . DIRECTORY_SEPARATOR;

        if (
            str_contains($file, 'codecept.phar/') ||
            str_contains($file, $vendor . 'phpunit') ||
            str_contains($file, $vendor . 'codeception')
        ) {
            return true;
        }

        $modulePath = 'src' . DIRECTORY_SEPARATOR . 'Codeception' . DIRECTORY_SEPARATOR . 'Module';
        if (str_contains($file, $modulePath)) {
            return false;
        }

        return str_contains($file, 'src' . DIRECTORY_SEPARATOR . 'Codeception' . DIRECTORY_SEPARATOR);
    }

    private static function frameExists(array $trace, string $file, int $line): bool
    {
        foreach ($trace as $frame) {
            if (($frame['file'] ?? null) === $file && ($frame['line'] ?? null) === $line) {
                return true;
            }
        }
        return false;
    }
}
