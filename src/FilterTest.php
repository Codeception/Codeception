<?php
namespace Codeception\PHPUnit;

use Codeception\Test\Descriptor;

/**
 * Extended Filter Test from PHPUnit to use Codeception's Descriptor to locate tests.
 *
 * Class FilterTest
 * @package Codeception\PHPUnit
 */
class FilterTest extends \PHPUnit\Runner\Filter\NameFilterIterator
{
    public function accept()
    {
        $test = $this->getInnerIterator()->current();

        if ($test instanceof \PHPUnit\Framework\TestSuite) {
            return true;
        }

        $name = Descriptor::getTestSignature($test);
        $accepted = preg_match($this->filter, $name, $matches);

        if ($accepted && isset($this->filterMax)) {
            $set = end($matches);
            $accepted = $set >= $this->filterMin && $set <= $this->filterMax;
        }
        return $accepted;
    }
}
