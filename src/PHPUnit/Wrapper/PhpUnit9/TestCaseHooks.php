<?php

declare(strict_types=1);

namespace Codeception\PHPUnit\Wrapper;

trait TestCaseHooks
{
    /**
     * @before
     */
    public function _codeceptSetUp(): void
    {
        if (method_exists($this, '_setUp')) {
            $this->_setUp();
        }
    }

    /**
     * @after
     */
    public function _codeceptTearDown(): void
    {
        if (method_exists($this, '_tearDown')) {
            $this->_tearDown();
        }
    }
}
