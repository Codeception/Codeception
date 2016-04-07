<?php
namespace Codeception\PHPUnit;

use Codeception\Test\Descriptor;

class FilterTest extends \PHPUnit_Runner_Filter_Test
{
    public function accept()
    {
        $test = $this->getInnerIterator()->current();

        if ($test instanceof \PHPUnit_Framework_TestSuite) {
            return true;
        }

        $name = Descriptor::getTestSignature($test);

        $accepted = preg_match($this->filter, $name, $matches);

        if ($accepted && isset($this->filterMax)) {
            $set      = end($matches);
            $accepted = $set >= $this->filterMin && $set <= $this->filterMax;
        }

        return $accepted;
    }

}