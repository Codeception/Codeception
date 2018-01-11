<?php

namespace Codeception\Step\Argument;

class PasswordArgument implements FormattedOutput
{
    /**
     * @var string
     */
    private $password;

    public function __construct($password)
    {
        $this->password = $password;
    }

    /**
     * {@inheritdoc}
     */
    public function getOutput()
    {
        return '******';
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->password;
    }
}
