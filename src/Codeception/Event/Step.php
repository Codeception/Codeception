<?php
namespace Codeception\Event;

use \Symfony\Component\EventDispatcher\Event;

class Step extends Test
{
    /**
     * @var \Codeception\Step
     */
    protected $step;

    public function __construct(\Codeception\TestCase $test, \Codeception\Step $step) {
        $this->test = $test;
        $this->step = $step;
    }


    public function getStep() {
        return $this->step;
    }

}
