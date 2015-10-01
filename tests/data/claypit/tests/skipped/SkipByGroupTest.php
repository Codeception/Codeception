<?php

class SkipByGroupTest extends \Codeception\Test\Unit
{
    /**
     * @group abc
     */
    public function testSkip()
    {
        $this->assertTrue(true);
    }

}