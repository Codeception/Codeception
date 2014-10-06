<?php
if (!class_exists('PHPUnit_Framework_RiskyTestError', false)) {
    class PHPUnit_Framework_RiskyTestError extends \PHPUnit_Framework_AssertionFailedError implements PHPUnit_Framework_RiskyTest
    {
        function __construct($message = "", $code = 0, \Exception $previous = null)
        {
            throw new \PHPUnit_Framework_Error($message, $code, __FILE__, $previous);
        }

    }
}

