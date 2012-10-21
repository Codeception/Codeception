<?php
namespace Codeception\Event;

use \Symfony\Component\EventDispatcher\Event;

class Suite extends Event
{
    protected $suite;

    public function __construct(\PHPUnit_Framework_TestSuite $suite) {
        $this->suite = $suite;
    }

    public function getSuite() {
        return $this->suite;
    }
}
