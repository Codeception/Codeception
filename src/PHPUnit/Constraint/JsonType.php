<?php

declare(strict_types=1);

namespace Codeception\PHPUnit\Constraint;

use Codeception\Util\JsonArray;
use Codeception\Util\JsonType as JsonTypeUtil;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;

use function json_encode;

class JsonType extends Constraint
{
    /**
     * @var array
     */
    protected $jsonType;
    /**
     * @var bool
     */
    private $match;

    public function __construct(array $jsonType, bool $match = true)
    {
        $this->jsonType = $jsonType;
        $this->match = $match;
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $jsonArray Value or object to evaluate.
     */
    protected function matches($jsonArray): bool
    {
        if ($jsonArray instanceof JsonArray) {
            $jsonArray = $jsonArray->toArray();
        }

        $matched = (new JsonTypeUtil($jsonArray))->matches($this->jsonType);

        if ($this->match) {
            if ($matched !== true) {
                throw new ExpectationFailedException($matched);
            }
        } elseif ($matched === true) {
            $jsonArray = json_encode($jsonArray, JSON_THROW_ON_ERROR);
            throw new ExpectationFailedException('Unexpectedly response matched: ' . $jsonArray);
        }
        return true;
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
