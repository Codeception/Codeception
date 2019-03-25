<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use PHPUnit\Framework\AssertionFailedError;

class Retry extends \Codeception\Module
{
    protected $fails = 0;
    protected $time;

    public function _before(\Codeception\TestInterface $test)
    {
        $this->fails = 0;
        $this->time = microtime(true);
    }

    public function failAt($amount = 3)
    {
        if ($this->fails < $amount) {
            $this->fails++;
            throw new AssertionFailedError('Failed as expected');
        }
    }

    public function failFor($sec = 0.2)
    {
        if ($this->time + $sec > microtime(true)) {
            throw new AssertionFailedError('Failed as expected');
        }
    }
}
