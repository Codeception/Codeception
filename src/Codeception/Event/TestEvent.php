<?php
namespace Codeception\Event;

use Symfony\Component\EventDispatcher\Event;

class TestEvent extends Event
{
    /**
     * @var \PHPUnit\Framework\Test
     */
    protected $test;

    /**
     * @var float Time taken
     */
    protected $time;

    public function __construct(\PHPUnit\Framework\Test $test, $time = 0)
    {
        $this->test = $test;
        $this->time = $time;
    }

    /**
     * @return float
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @return \Codeception\TestInterface
     */
    public function getTest()
    {
        return $this->test;
    }
}
