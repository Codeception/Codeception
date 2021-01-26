<?php

namespace Codeception\Step\Argument;

/**
 * Implemented in Step arguments where literal values need to be modified in test execution output (e.g. passwords).
 */
interface FormattedOutput
{
    /**
     * Returns the argument's value formatted for output.
     */
    public function getOutput(): string;

    /**
     * Returns the argument's literal value.
     */
    public function __toString(): string;
}
