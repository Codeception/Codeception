<?php
namespace Codeception\Event;

use \Symfony\Component\EventDispatcher\Event;

class Suite extends Event
{
    protected $suite;
    protected $result;
    protected $settings;


    public function __construct(\PHPUnit_Framework_TestSuite $suite, $settings = array(), \PHPUnit_Framework_TestResult $result = null) {
        $this->suite = $suite;
        $this->result = $result;
        $this->settings = $settings;
    }

    /**
     * @return \PHPUnit_Framework_TestSuite
     */
    public function getSuite() {
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
