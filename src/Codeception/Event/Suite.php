<?php
namespace Codeception\Event;

use \Symfony\Component\EventDispatcher\Event;

class Suite extends Event
{
    protected $suite;
    protected $result;

    public function __construct(\PHPUnit_Framework_TestSuite $suite, \PHPUnit_Framework_TestResult $result = null) {
        $this->suite = $suite;
        $this->result = $result;
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



}
