<?php

class SkipByGroupTest extends \Codeception\TestCase\Test
{
    /**
     * @group abc
     */
    public function testSkip()
    {
        $this->assertTrue(true);
    }

}