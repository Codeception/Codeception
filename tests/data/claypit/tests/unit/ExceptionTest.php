<?php 

class ExceptionTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @group error
     */
    public function testError()
    {
        throw new \RuntimeException('Helllo!');
    }
} 