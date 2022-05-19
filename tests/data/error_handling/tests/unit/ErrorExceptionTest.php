<?php

class ErrorExceptionTest extends \PHPUnit\Framework\TestCase
{
    
    public function test_notice()
    {
        $this->expectNotice();
        $this->expectNoticeMessage('foobar');
        trigger_error('foobar', E_USER_NOTICE);
    }
    
    public function test_warning()
    {
        $this->expectWarning();
        $this->expectWarningMessage('foobar');
        trigger_error('foobar', E_USER_WARNING);
    }
    
    public function test_error()
    {
        $this->expectError();
        $this->expectErrorMessage('foobar');
        trigger_error('foobar', E_USER_ERROR);
    }
    
    public function test_deprecation()
    {
        // This test fails.
        $this->expectDeprecation();
        $this->expectDeprecationMessage('foobar');
        trigger_error('foobar', E_USER_DEPRECATED);
    }
    
}