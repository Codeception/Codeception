<?php

declare(strict_types=1);

namespace Codeception\Coverage;

use PHPUnit\Runner\CodeCoverage as PHPUnitCodeCoverage;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Driver;
use SebastianBergmann\CodeCoverage\Filter as CodeCoverageFilter;
use function method_exists;

class PhpCodeCoverageFactory
{
    public static function build(): CodeCoverage
    {
        $filter = new CodeCoverageFilter();
        if (method_exists(PHPUnitCodeCoverage::class, 'activate')) {
            // php-code-coverage 10
            if (!PHPUnitCodeCoverage::isActive()) {
                PHPUnitCodeCoverage::activate($filter, false);
            }
            return PHPUnitCodeCoverage::instance();
        } else {
            //php-code-coverage 9+
            $filter = new CodeCoverageFilter();
            $driver = Driver::forLineCoverage($filter);

            return new CodeCoverage($driver, $filter);
        }
    }
}
