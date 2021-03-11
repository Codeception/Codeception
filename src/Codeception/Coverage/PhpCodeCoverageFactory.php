<?php

declare(strict_types=1);

namespace Codeception\Coverage;

use Codeception\PHPUnit\Compatibility\PHPUnit10;
use PHPUnit\Runner\CodeCoverage as PHPUnitCodeCoverage;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Driver;
use SebastianBergmann\CodeCoverage\Filter as CodeCoverageFilter;

class PhpCodeCoverageFactory
{
    public static function build(): CodeCoverage
    {
        $filter = new CodeCoverageFilter();
        if (PHPUnit10::codeCoverageRunnerClassExists()) {
            if (!PHPUnitCodeCoverage::isActive()) {
                PHPUnitCodeCoverage::activate($filter, false);
            }
            return PHPUnitCodeCoverage::instance();
        } else {
            $filter = new CodeCoverageFilter();
            $driver = Driver::forLineCoverage($filter);

            return new CodeCoverage($driver, $filter);
        }
    }
}
