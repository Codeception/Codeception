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

    public function __construct(\PHPUnit\Framework\Test $test, $time, \Exception $e, $count = 0)
    {
        parent::__construct($test, $time);
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
