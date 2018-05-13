<?php

namespace Codeception\PHPUnit\Constraint;

use Codeception\Util\JsonType as JsonTypeUtil;
use Codeception\Util\JsonArray;

class JsonType extends \PHPUnit\Framework\Constraint\Constraint
{
    protected $jsonType;
    private $match;

    public function __construct(array $jsonType, $match = true)
    {
        parent::__construct();
        $this->jsonType = $jsonType;
        $this->match = $match;
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $jsonArray Value or object to evaluate.
     *
     * @return bool
     */
    protected function matches($jsonArray) : bool
    {
        if ($jsonArray instanceof JsonArray) {
            $jsonArray = $jsonArray->toArray();
        }

        $matched = (new JsonTypeUtil($jsonArray))->matches($this->jsonType);

        if ($this->match) {
            if ($matched !== true) {
                throw new \PHPUnit\Framework\ExpectationFailedException($matched);
            }
        } else {
            if ($matched === true) {
                throw new \PHPUnit\Framework\ExpectationFailedException('Unexpectedly response matched: ' . json_encode($jsonArray));
            }
        }
        return true;
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
