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
        if (self::$instance !== null) {
            return self::$instance;
        }

        $coverageConfiguration = Configuration::config()['coverage'];
        $pathCoverage = $coverageConfiguration['path_coverage'] ?? false;

        $filter = new CodeCoverageFilter();
        if ($pathCoverage) {
            $driver = (new Selector())->forLineAndPathCoverage($filter);
        } else {
            $driver = (new Selector())->forLineCoverage($filter);
        }
        self::$instance = new CodeCoverage($driver, $filter);

        return self::$instance;
    }

    public static function clear(): void
    {
        self::$instance = null;
    }
}
