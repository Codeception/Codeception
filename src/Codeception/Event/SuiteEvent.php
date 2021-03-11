<?php

declare(strict_types=1);

namespace Codeception\Event;

use Codeception\Suite;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestSuite;
use Symfony\Contracts\EventDispatcher\Event;

class SuiteEvent extends Event
{
    /**
     * @var TestSuite
     */
    protected $suite;

    /**
     * @var TestResult
     */
    protected $result;

    /**
     * @var array
     */
    protected $settings = [];

    public function __construct(
        TestSuite $testSuite,
        TestResult $testResult = null,
        array $settings = []
    ) {
        $this->suite = $testSuite;
        $this->result = $testResult;
        $this->settings = $settings;
    }

    public function getSuite(): TestSuite
    {
        return $this->suite;
    }

    public function getResult(): TestResult
    {
        return $this->result;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }
}
