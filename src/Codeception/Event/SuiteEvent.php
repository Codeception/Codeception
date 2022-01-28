<?php

declare(strict_types=1);

namespace Codeception\Event;

use Codeception\Suite;
use PHPUnit\Framework\TestResult;
use Symfony\Contracts\EventDispatcher\Event;

class SuiteEvent extends Event
{
    protected ?Suite $suite;

    protected ?TestResult $result;

    protected array $settings;

    public function __construct(
        ?Suite $Suite = null,
        ?TestResult $testResult = null,
        array $settings = []
    ) {
        $this->suite = $Suite;
        $this->result = $testResult;
        $this->settings = $settings;
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
