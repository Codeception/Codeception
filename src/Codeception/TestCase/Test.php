<?php
namespace Codeception\TestCase;

class Test extends \Codeception\TestCase implements \PHPUnit_Framework_SelfDescribing
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $dispatcher = null;
    protected $bootstrap = null;

    public function setDispatcher($dispatcher) {
        $this->dispatcher = $dispatcher;
    }

    public function setBootstrap($bootstrap) {
        $this->bootstrap = $bootstrap;
    }

}
