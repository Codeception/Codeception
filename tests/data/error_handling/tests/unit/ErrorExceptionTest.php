<?php

class ErrorExceptionTest extends \PHPUnit\Framework\TestCase
{
    
    public function testNotice()
    {
        $this->expectNotice();
        $this->expectNoticeMessage('foobar');
        trigger_error('foobar', E_USER_NOTICE);
    }

    public function testWarning()
    {
        $this->expectWarning();
        $this->expectWarningMessage('foobar');
        trigger_error('foobar', E_USER_WARNING);
    }

    public function testError()
    {
        $this->expectError();
        $this->expectErrorMessage('foobar');
        trigger_error('foobar', E_USER_ERROR);
    }

    public function testDeprecation()
    {
        // This test fails.
        $this->expectDeprecation();
        $this->expectDeprecationMessage('foobar');
        trigger_error('foobar', E_USER_DEPRECATED);
    }
}
