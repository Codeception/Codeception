<?php
namespace Codeception\Event;

use \Symfony\Component\EventDispatcher\Event;

class Fail extends Test
{
    /**
     * @var \Exception
     */
    protected $fail;

    protected $count;


    public function __construct(\PHPUnit_Framework_Test $test, \Exception $e, $count = 0) {
        $this->test = $test;
        $this->fail = $e;
        $this->count = $count;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function getFail() {
        return $this->fail;
    }

}
