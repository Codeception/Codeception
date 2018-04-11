<?php
namespace Codeception\Event;

use Codeception\Suite;
use Symfony\Component\EventDispatcher\Event;

class SuiteEvent extends Event
{
    /**
     * @var \PHPUnit\Framework\TestSuite
     */
    protected $suite;

    /**
     * @var \PHPUnit\Framework\TestResult
     */
    protected $result;

    /**
     * @var array
     */
    protected $settings;

    public function __construct(
        \PHPUnit\Framework\TestSuite $suite,
        \PHPUnit\Framework\TestResult $result = null,
        $settings = []
    ) {
        $this->suite = $suite;
        $this->result = $result;
        $this->settings = $settings;
    }

    /**
     * @return Suite
     */
    public function getSuite()
    {
        return $this->suite;
    }

    /**
     * @return \PHPUnit\Framework\TestResult
     */
    public function getResult()
    {
        return $this->result;
    }

    public function getSettings()
    {
        return $this->settings;
    }
}
