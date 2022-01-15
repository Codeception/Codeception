<?php

declare(strict_types=1);

namespace Codeception\Coverage;

use PHPUnit\Runner\CodeCoverage as PHPUnitCodeCoverage;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Filter as CodeCoverageFilter;

class PhpCodeCoverageFactory
{
    public static function build(): CodeCoverage
    {
        $filter = new CodeCoverageFilter();
        if (!PHPUnitCodeCoverage::isActive()) {
            PHPUnitCodeCoverage::activate($filter, false);
        }
        return PHPUnitCodeCoverage::instance();
    }
}
