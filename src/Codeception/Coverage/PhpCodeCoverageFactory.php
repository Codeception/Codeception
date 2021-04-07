<?php

declare(strict_types=1);

namespace Codeception\Coverage;

use Codeception\Configuration;
use Codeception\PHPUnit\Compatibility\PHPUnit10;
use PHPUnit\Runner\CodeCoverage as PHPUnitCodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Filter as CodeCoverageFilter;

class PhpCodeCoverageFactory
{
    public static function build(): CodeCoverage
    {
        $filter = new CodeCoverageFilter();
        $coverageConfiguration = Configuration::config()['coverage'];
        if (PHPUnit10::codeCoverageRunnerClassExists()) {
            if (!PHPUnitCodeCoverage::isActive()) {
                PHPUnitCodeCoverage::activate($filter, $coverageConfiguration['path_coverage'] ?? false);
            }
            return PHPUnitCodeCoverage::instance();
        } else {
            $filter = new CodeCoverageFilter();
            if ($coverageConfiguration['path_coverage'] ?? false) {
                $driver = (new Selector)->forLineAndPathCoverage($filter);
            } else {
                $driver = (new Selector)->forLineCoverage($filter);
            }

            return new CodeCoverage($driver, $filter);
        }
    }
}
