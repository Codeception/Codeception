<?php

declare(strict_types=1);

namespace Codeception\Coverage;

use Codeception\Configuration;
use PHPUnit\Runner\CodeCoverage as PHPUnitCodeCoverage;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Driver;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\Filter as CodeCoverageFilter;

use function class_exists;

class PhpCodeCoverageFactory
{
    public static function build(): CodeCoverage
    {
        $coverageConfiguration = Configuration::config()['coverage'];
        $pathCoverage = $coverageConfiguration['path_coverage'] ?? false;

        if (class_exists(PHPUnitCodeCoverage::class)) {
            // PHPUnit 10 (php-code-coverage 10)
            $filter = new CodeCoverageFilter();
            if (!PHPUnitCodeCoverage::isActive()) {
                PHPUnitCodeCoverage::activate($filter, $pathCoverage);
            }
            return PHPUnitCodeCoverage::instance();
        } else {
            // PHPUnit 9 (php-code-coverage 9)
            $filter = new CodeCoverageFilter();
            if ($pathCoverage) {
                $driver = (new Selector())->forLineAndPathCoverage($filter);
            } else {
                $driver = (new Selector())->forLineCoverage($filter);
            }
            return new CodeCoverage($driver, $filter);
        }
    }
}
