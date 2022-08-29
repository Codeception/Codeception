<?php

declare(strict_types=1);

namespace Codeception\Event;

use Codeception\ResultAggregator;
use Codeception\Suite;
use Symfony\Contracts\EventDispatcher\Event;

class SuiteEvent extends Event
{
    public function __construct(protected ?Suite $suite = null, protected array $settings = [])
    {
    }

    public function getSuite(): ?Suite
    {
        return $this->suite;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }
}
