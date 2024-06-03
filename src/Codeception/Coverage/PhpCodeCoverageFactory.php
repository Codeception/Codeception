<?php

declare(strict_types=1);

namespace Codeception\Coverage;

use Codeception\Configuration;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\Filter as CodeCoverageFilter;

class PhpCodeCoverageFactory
{
    private static ?CodeCoverage $instance = null;

    public static function build(): CodeCoverage
    {
        if (self::$instance instanceof CodeCoverage) {
            return self::$instance;
        }

        $coverageConfig = Configuration::config()['coverage'];
        $pathCoverage = $coverageConfig['path_coverage'] ?? false;

        $filter = new CodeCoverageFilter();
        $selector = new Selector();
        $driver = $pathCoverage ? $selector->forLineAndPathCoverage($filter) : $selector->forLineCoverage($filter);

        return self::$instance = new CodeCoverage($driver, $filter);
    }

    public static function clear(): void
    {
        self::$instance = null;
    }
}
