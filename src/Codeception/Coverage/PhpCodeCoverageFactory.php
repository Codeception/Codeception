<?php

declare(strict_types=1);

namespace Codeception\Coverage;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Driver;
use SebastianBergmann\CodeCoverage\Filter as CodeCoverageFilter;
use function method_exists;

class PhpCodeCoverageFactory
{
    public static function build(): CodeCoverage
    {
        if (method_exists(Driver::class, 'forLineCoverage')) {
            //php-code-coverage 9+
            $filter = new CodeCoverageFilter();
            $driver = Driver::forLineCoverage($filter);
            return new CodeCoverage($driver, $filter);
        } else {
            //php-code-coverage 8 or older
            return new CodeCoverage();
        }
    }
}
