<?php

declare(strict_types=1);

namespace Codeception\Event;

use Codeception\Step;
use Codeception\TestInterface;
use Symfony\Contracts\EventDispatcher\Event;

class StepEvent extends Event
{
    public function __construct(protected TestInterface $test, protected Step $step)
    {
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
