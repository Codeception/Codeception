<?php

declare(strict_types=1);

namespace Codeception\Event;

use Codeception\Suite;
use PHPUnit\Framework\TestResult;
use Symfony\Contracts\EventDispatcher\Event;

class SuiteEvent extends Event
{
    public function __construct(protected ?Suite $suite = null, protected ?TestResult $result = null, protected array $settings = [])
    {
    }

    public function getSuite(): ?Suite
    {
        return $this->suite;
    }

    public function getResult(): ?TestResult
    {
        return $this->result;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }
}
