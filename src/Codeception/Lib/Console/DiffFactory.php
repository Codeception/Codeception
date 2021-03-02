<?php

declare(strict_types=1);

namespace Codeception\Lib\Console;

use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Diff\Differ;

/**
 * DiffFactory
 **/
class DiffFactory
{
    public function createDiff(ComparisonFailure $failure): ?string
    {
        $diff = $this->getDiff($failure->getExpectedAsString(), $failure->getActualAsString());
        if ($diff === '') {
            return null;
        }

        return $diff;
    }

    private function getDiff(string $expected = '', string $actual = ''): string
    {
        if (!$actual && !$expected) {
            return '';
        }

        $differ = new Differ('');

        return $differ->diff($expected, $actual);
    }
}
