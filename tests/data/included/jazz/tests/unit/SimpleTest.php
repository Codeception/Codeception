<?php

namespace Jazz;

class SimpleTest extends \Codeception\Test\Unit
{
    /**
     * @var \Jazz\UnitGuy
     */
    protected UnitGuy $guy;

    // tests
    public function testSimple()
    {
        $this->assertTrue(true);
    }

    public function testSimpler()
    {
        $this->assertTrue(true);
    }
}
