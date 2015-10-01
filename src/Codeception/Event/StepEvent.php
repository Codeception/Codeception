<?php
namespace Codeception\Event;

use Codeception\Step;
use Codeception\TestInterface;

class StepEvent extends TestEvent
{
    /**
     * @var Step
     */
    protected $step;

    public function __construct($test, Step $step)
    {
        $this->test = $test;
        $this->step = $step;
    }

    public function getStep()
    {
        return $this->step;
    }
}
