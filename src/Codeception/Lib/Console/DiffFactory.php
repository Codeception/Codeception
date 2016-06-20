<?php
namespace Codeception\Lib\Console;

use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Diff\Differ;

/**
 * DiffFactory
 **/
class DiffFactory
{
    /**
     * @param ComparisonFailure $failure
     * @return string|null
     */
    public function createDiff(ComparisonFailure $failure)
    {
        $diff = $this->getDiff($failure->getExpectedAsString(), $failure->getActualAsString());
        if (!$diff) {
            return null;
        }

        return $diff;
    }

    /**
     * @param string $expected
     * @param string $actual
     * @return string
     */
    private function getDiff($expected = '', $actual = '')
    {
        if (!$actual && !$expected) {
            return '';
        }

        $differ = new Differ('');

        return $differ->diff($expected, $actual);
    }
}
