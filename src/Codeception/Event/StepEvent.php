<?php

declare(strict_types=1);

namespace Codeception\Event;

use Codeception\Step;
use Codeception\TestInterface;
use Symfony\Contracts\EventDispatcher\Event;

class StepEvent extends Event
{
    protected Step $step;

    protected TestInterface $test;

    public function __construct(TestInterface $test, Step $step)
    {
        $this->test = $test;
        $this->step = $step;
    }

    public function getStep(): Step
    {
        return $this->step;
    }

    public function getTest(): TestInterface
    {
        return $this->test;
    }
}
