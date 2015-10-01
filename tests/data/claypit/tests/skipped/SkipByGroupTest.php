<?php

class SkipByGroupTest extends \Codeception\Test\TestCase
{
    /**
     * @group abc
     */
    public function testSkip()
    {
        $this->assertTrue(true);
    }

}