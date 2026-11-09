<?php

declare(strict_types=1);

namespace Codeception\Step\Argument;

use Stringable;

/**
 * Stands in for an object argument of a finished step, see the `release_step_arguments` setting.
 *
 * @internal
 */
final class ReleasedArgument implements FormattedOutput, Stringable
{
    public function __construct(private readonly string $output)
    {
    }

    public function getOutput(): string
    {
        return $this->output;
    }

    public function __toString(): string
    {
        return $this->output;
    }
}
