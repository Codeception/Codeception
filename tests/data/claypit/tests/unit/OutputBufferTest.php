<?php
use Codeception\TestCase\Test;

/**
 * @group output
 * Class OutputBufferTest
 */
class OutputBufferTest extends Test
{
    public function testBar()
    {
        ob_start();
        throw new \Exception();
    }
} 