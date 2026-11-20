<?php

declare(strict_types=1);

namespace Codeception\PHPUnit\Wrapper;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;

trait TestCaseHooks
{
    #[Before(PHP_INT_MAX)]
    public function _codeceptSetUp(): void
    {
        if (method_exists($this, '_setUp')) {
            $this->_setUp();
        }
    }

    #[After(PHP_INT_MIN)]
    public function _codeceptTearDown(): void
    {
        if (method_exists($this, '_tearDown')) {
            $this->_tearDown();
        }
    }
}
