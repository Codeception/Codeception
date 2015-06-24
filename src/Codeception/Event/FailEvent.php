<?php
namespace Codeception\Event;

class FailEvent extends TestEvent
{
    /**
     * @var \Exception
     */
    protected $fail;

    /**
     * @var int
     */
    protected $count;

    public function __construct(\PHPUnit_Framework_Test $test, \Exception $e, $count = 0)
    {
        $this->test = $test;
        $this->fail = $e;
        $this->count = $count;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function getFail()
    {
        return $this->fail;
    }
}
