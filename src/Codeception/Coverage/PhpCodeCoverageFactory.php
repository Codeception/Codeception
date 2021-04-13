<?php

declare(strict_types=1);

namespace Codeception\Coverage;

use Codeception\Configuration;
use PHPUnit\Runner\CodeCoverage as PHPUnitCodeCoverage;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Filter as CodeCoverageFilter;

class PhpCodeCoverageFactory
{
    public static function build(): CodeCoverage
    {
        $filter = new CodeCoverageFilter();
        if (!PHPUnitCodeCoverage::isActive()) {
            $coverageConfiguration = Configuration::config()['coverage'];
            PHPUnitCodeCoverage::activate($filter, $coverageConfiguration['path_coverage'] ?? false);
        }
        return PHPUnitCodeCoverage::instance();
    }
}
