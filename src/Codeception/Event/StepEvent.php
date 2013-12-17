<?php
namespace Codeception\Event;

use Codeception\Step;
use Codeception\TestCase;

class StepEvent extends TestEvent
{
    /**
     * @var Step
     */
    protected $step;

    public function __construct(TestCase $test, Step $step)
    {
        $this->test = $test;
        $this->step = $step;
    }

    public function getStep()
    {
        return $this->step;
    }
}
