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

    public function testMaybe() {
        $this->maybe = new \Codeception\Maybe("Hello");
        $this->assertEquals('Hello', $this->maybe);
    }

}