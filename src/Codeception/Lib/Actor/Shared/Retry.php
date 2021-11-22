<?php

declare(strict_types=1);

namespace Codeception\Lib\Actor\Shared;

trait Retry
{
    protected int $retryNum = 1;

    protected int $retryInterval = 100;

    /**
     * Configure number of retries and initial interval.
     * Interval will be doubled on each unsuccessful execution.
     *
     * Use with \$I->retryXXX() methods;
     */
    public function retry(int $num, int $interval = 200): void
    {
        $this->retryNum = $num;
        $this->retryInterval = $interval;
    }
}
