<?php

class SkipByGroupTest extends \Codeception\Test\Format\TestCase
{
    /**
     * @group abc
     */
    public function testSkip()
    {
        $this->assertTrue(true);
    }

}