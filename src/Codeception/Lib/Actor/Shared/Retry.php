<?php

namespace Codeception\Lib\Actor\Shared;

trait Retry
{
    protected $retryNum = 1;
    protected $retryInterval = 100;

    /**
     * Configure number of retries and initial interval.
     * Interval will be doubled on each unsuccessful execution.
     *
     * Use with \$I->retryXXX() methods;
     *
     * @param $num
     * @param int $interval
     */
    public function retry($num, $interval = 200)
    {
        $this->retryNum = $num;
        $this->retryInterval = $interval;
    }
}
