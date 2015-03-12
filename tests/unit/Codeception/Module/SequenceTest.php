<?php

class SequenceTest extends Codeception\TestCase\Test
{
    // tests
    public function testSequences()
    {
        $module = new \Codeception\Module\Sequence(make_container());
        $this->assertNotEquals(sq(), sq());
        $this->assertNotEquals(sq(1), sq(2));
        $this->assertEquals(sq(1), sq(1));
        $old = sq(1);
        $module->_after($this);
        $this->assertNotEquals($old, sq(1));
    }

}