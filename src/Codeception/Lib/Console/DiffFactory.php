<?php

declare(strict_types=1);

namespace Codeception\Lib\Console;

use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\StrictUnifiedDiffOutputBuilder;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;

class DiffFactory
{
    public function createDiff(ComparisonFailure $failure): string
    {
        return $this->getDiff($failure->getExpectedAsString(), $failure->getActualAsString());
    }

    private function getDiff(string $expected = '', string $actual = ''): string
    {
        // Ensure compatibility with sebastian/diff v8.3.0
        // v8.3.0 added the parameter $emitNoLineEndEofWarning
        // v9.0.0 removed the class UnifiedDiffOutputBuilder, therefore
        // use StrictUnifiedDiffOutputBuilder right away.
        if (
            !class_exists(UnifiedDiffOutputBuilder::class)
            || property_exists(UnifiedDiffOutputBuilder::class, 'emitNoLineEndEofWarning')
        ) {
            $outputBuilder = new StrictUnifiedDiffOutputBuilder([
                'addLineNumbers' => false,
                'emitNoLineEndEofWarning' => false,
                'header' => '',
            ]);
        } else {
            $outputBuilder = new UnifiedDiffOutputBuilder('');
        }
        $differ = new Differ($outputBuilder);
        return ($expected || $actual) ? $differ->diff($expected, $actual) : '';
    }
}
