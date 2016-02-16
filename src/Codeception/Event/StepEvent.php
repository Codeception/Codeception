<?php
namespace Codeception\Event;

use Codeception\Step;
use Codeception\TestInterface;
use Symfony\Component\EventDispatcher\Event;

class StepEvent extends Event
{
    /**
     * @var Step
     */
    protected $step;

    /**
     * @var TestInterface
     */
    protected $test;

    public function __construct(TestInterface $test, Step $step)
    {
        $this->test = $test;
        $this->step = $step;
    }

    public function getStep()
    {
        return $this->step;
    }

    /**
     * @return TestInterface
     */
    public function getTest()
    {
        return $this->test;
    }
}
