<?php

use Codeception\Util\Stub;

class MaybeTest extends \Codeception\TestCase\Test
{
   /**
    * @var CodeGuy
    */
    protected $codeGuy;

    /**
     * @var \Codeception\Maybe
     */
    protected $maybe;

    // keep this setupUp and tearDown to enable proper work of Codeception modules
    protected function setUp()
    {
        if ($this->bootstrap) require $this->bootstrap;
        $this->dispatcher->dispatch('test.before', new \Codeception\Event\Test($this));
        $this->codeGuy = new CodeGuy($scenario = new \Codeception\Scenario($this));
        $scenario->run();
    }

    protected function tearDown()
    {
        $this->dispatcher->dispatch('test.after', new \Codeception\Event\Test($this));
    }

    public function testMaybe() {
        $this->maybe = new \Codeception\Maybe("Hello");
        $this->assertEquals('Hello', $this->maybe);
    }

}