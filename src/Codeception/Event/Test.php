<?php
namespace Codeception\Event;

use \Symfony\Component\EventDispatcher\Event;

class Test extends Event
{
    /**
     * @var \PHPUnit_Framework_Test
     */
    protected $test;

    public function __construct(\PHPUnit_Framework_Test $test) {
        $this->test = $test;
    }

    /**
     * @return \Codeception\TestCase
     */
    public function getTest() {
        return $this->test;
    }

}