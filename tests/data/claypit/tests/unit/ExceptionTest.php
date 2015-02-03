<?php 

class ExceptionTest extends PHPUnit_Framework_TestCase
{

    /**
     * @group error
     */
    public function testError()
    {
        throw new \RuntimeException('Helllo!');
    }
} 