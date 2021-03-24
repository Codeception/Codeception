<?php

declare(strict_types=1);

namespace Codeception\PHPUnit\Compatibility;

use PHPUnit\Runner\CodeCoverage;
use function method_exists;

class PHPUnit10
{
    public static function codeCoverageRunnerClassExists(): bool
    {
        return class_exists(CodeCoverage::class);
    }

    public static function numberOfAssertionsPerformedMethodExists(object $test): bool
    {
        return method_exists($test, 'numberOfAssertionsPerformed');
    }
}
