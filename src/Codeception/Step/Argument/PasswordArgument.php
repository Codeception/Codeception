<?php

declare(strict_types=1);

namespace Codeception\Step\Argument;

use Stringable;

class PasswordArgument implements FormattedOutput, Stringable
{
    public function __construct(private readonly string $password)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getOutput(): string
    {
        return '******';
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return $this->password;
    }
}
