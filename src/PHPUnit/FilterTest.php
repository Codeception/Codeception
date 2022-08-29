<?php

declare(strict_types=1);

namespace Codeception\PHPUnit;

use Codeception\PHPUnit\NonFinal\NameFilterIterator;
use Codeception\Test\Descriptor;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\WarningTestCase;
use function end;
use function is_null;
use function preg_match;

/**
 * Extended Filter Test from PHPUnit to use Codeception's Descriptor to locate tests.
 *
 * Class FilterTest
 * @package Codeception\PHPUnit
 */
class FilterTest extends NameFilterIterator
{
    public function accept(): bool
    {
        $test = $this->getInnerIterator()->current();

        if ($test instanceof TestSuite) {
            return true;
        }

        $name = Descriptor::getTestSignature($test);
        $index = Descriptor::getTestDataSetIndex($test);

        if (!is_null($index)) {
            $name .= " with data set #{$index}";
        }

        $accepted = preg_match($this->filter, $name, $matches);

        // This fix the issue when an invalid DataProvider method generate a warning
        // See https://github.com/Codeception/Codeception/issues/4888
        if ($test instanceof WarningTestCase) {
            $message = $test->getMessage();
            $accepted = preg_match($this->filter, $message, $matches);
        }

        if ($accepted && $this->filterMax !== null) {
            $set = end($matches);
            $accepted = $set >= $this->filterMin && $set <= $this->filterMax;
        }
        return (bool) $accepted;
    }
}
