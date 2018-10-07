<?php

namespace Codeception\PHPUnit\Constraint;

use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\ArrayComparator;
use SebastianBergmann\Comparator\Factory;
use Codeception\Util\JsonArray;

class JsonContains extends \PHPUnit\Framework\Constraint\Constraint
{
    /**
     * @var
     */
    protected $expected;

    public function __construct(array $expected)
    {
        parent::__construct();
        $this->expected = $expected;
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $other Value or object to evaluate.
     *
     * @return bool
     */
    protected function matches($other) : bool
    {
        $jsonResponseArray = new JsonArray($other);
        if (!is_array($jsonResponseArray->toArray())) {
            throw new \PHPUnit\Framework\AssertionFailedError('JSON response is not an array: ' . $other);
        }

        if ($jsonResponseArray->containsArray($this->expected)) {
            return true;
        }

        $comparator = new ArrayComparator();
        $comparator->setFactory(new Factory);
        try {
            $comparator->assertEquals($this->expected, $jsonResponseArray->toArray());
        } catch (ComparisonFailure $failure) {
            throw new \PHPUnit\Framework\ExpectationFailedException(
                "Response JSON does not contain the provided JSON\n",
                $failure
            );
        }

        return false;
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @return string
     */
    public function toString() : string
    {
        //unused
        return '';
    }

    protected function failureDescription($other) : string
    {
        //unused
        return '';
    }
}
