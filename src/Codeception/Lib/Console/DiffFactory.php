<?php

declare(strict_types=1);

namespace Codeception\Lib\Console;

use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;

class DiffFactory
{
    public function createDiff(ComparisonFailure $failure): string
    {
        return $this->getDiff($failure->getExpectedAsString(), $failure->getActualAsString());
    }

    private function getDiff(string $expected = '', string $actual = ''): string
    {
        $differ = new Differ(new UnifiedDiffOutputBuilder(''));
        return ($expected || $actual) ? $differ->diff($expected, $actual) : '';
    }
}
