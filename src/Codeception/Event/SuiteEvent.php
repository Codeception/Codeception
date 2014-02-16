<?php

namespace Codeception\Event;

use Symfony\Component\EventDispatcher\Event;

class SuiteEvent extends Event
{
    /**
     * @var \PHPUnit_Framework_TestSuite
     */
    protected $suite;

    /**
     * @var \PHPUnit_Framework_TestResult
     */
    protected $result;

    /**
     * @var array
     */
    protected $settings;

    public function __construct(
        \PHPUnit_Framework_TestSuite $suite,
        \PHPUnit_Framework_TestResult $result = null,
        $settings = array()
    ) {
        $this->suite    = $suite;
        $this->result   = $result;
        $this->settings = $settings;
    }

    /**
     * @return \PHPUnit_Framework_TestSuite
     */
    public function getSuite()
    {
        return $this->suite;
    }

    /**
     * @return \PHPUnit_Framework_TestResult
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
