<?php

declare(strict_types=1);

namespace Codeception\PHPUnit\Constraint;

use Codeception\Util\JsonArray;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Comparator\ArrayComparator;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\Factory;

use function is_array;

class JsonContains extends Constraint
{
    /**
     * @var array
     */
    protected $expected;

    public function __construct(array $expected)
    {
        $this->expected = $expected;
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $other Value or object to evaluate.
     */
    protected function matches($other): bool
    {
        $jsonResponseArray = new JsonArray($other);
        if (!is_array($jsonResponseArray->toArray())) {
            throw new AssertionFailedError('JSON response is not an array: ' . $other);
        }
        $jsonArrayContainsArray = $jsonResponseArray->containsArray($this->expected);

        if ($jsonArrayContainsArray) {
            return true;
        }

        $comparator = new ArrayComparator();
        $comparator->setFactory(new Factory());
        try {
            $comparator->assertEquals($this->expected, $jsonResponseArray->toArray());
        } catch (ComparisonFailure $failure) {
            throw new ExpectationFailedException(
                "Response JSON does not contain the provided JSON\n",
                $failure
            );
        }

        return false;
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        //unused
        return '';
    }

    protected function failureDescription($other): string
    {
        //unused
        return '';
    }
}
