<?php

declare(strict_types=1);

namespace Codeception\Step\Argument;

class PasswordArgument implements FormattedOutput
{
    /**
     * @var string
     */
    private $password;

    public function __construct(string $password)
    {
        $this->password = $password;
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
