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

    public function testMaybe()
    {
        $this->maybe = new \Codeception\Maybe("Hello");
        $this->assertEquals('Hello', $this->maybe);
    }

    public function testMaybeClone()
    {
        // Cloning with an object
        $obj = new StdClass();
        $maybe = new \Codeception\Maybe($obj);
        $clone = clone $maybe;
        $this->assertNotSame($obj, $clone->__value());

        // Non object clone
        $maybe = new \Codeception\Maybe("Hello");
        $this->assertEquals('Hello', clone $maybe);

        $maybe = new \Codeception\Maybe(3);
        $clone = clone $maybe;
        $this->assertEquals(3, $clone->__value());

        $maybe = new \Codeception\Maybe(false);
        $clone = clone $maybe;
        $this->assertFalse($clone->__value());

        // Null clone
        $maybe = new \Codeception\Maybe(null);
        $clone = clone $maybe;
        $this->assertNull($clone->__value());

    }
}
