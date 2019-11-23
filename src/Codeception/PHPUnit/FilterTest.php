<?php
namespace Codeception\PHPUnit;

use Codeception\PHPUnit\NonFinal\NameFilterIterator;
use Codeception\Test\Descriptor;

/**
 * Extended Filter Test from PHPUnit to use Codeception's Descriptor to locate tests.
 *
 * Class FilterTest
 * @package Codeception\PHPUnit
 */
class FilterTest extends NameFilterIterator
{
    public function accept():bool
    {
        $test = $this->getInnerIterator()->current();

        if ($test instanceof \PHPUnit\Framework\TestSuite) {
            return true;
        }

        $name = Descriptor::getTestSignature($test);
        $index = Descriptor::getTestDataSetIndex($test);

        if (!is_null($index)) {
            $name .= " with data set #{$index}";
        }

        $accepted = preg_match($this->filter, $name, $matches);

        // This fix the issue when an invalid dataprovider method generate a warning
        // See issue https://github.com/Codeception/Codeception/issues/4888
        if($test instanceof \PHPUnit\Framework\WarningTestCase) {
            $message = $test->getMessage();
            $accepted = preg_match($this->filter, $message, $matches);
        }

        if ($accepted && isset($this->filterMax)) {
            $set = end($matches);
            $accepted = $set >= $this->filterMin && $set <= $this->filterMax;
        }
        return $accepted;
    }
}
