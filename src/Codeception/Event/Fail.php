<?php
namespace Codeception\Event;

use \Symfony\Component\EventDispatcher\Event;

class Fail extends Test
{
    /**
     * @var \Exception
     */
    protected $fail;

    public function __construct(\PHPUnit_Framework_Test $test, \Exception $e) {
        $this->test = $test;
        $this->fail = $e;
    }


    public function getFail() {
        return $this->fail;
    }

}
