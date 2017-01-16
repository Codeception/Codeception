<?php

namespace Codeception\Util;

/**
 * Holds matcher and value of mocked method
 */
class StubMarshaler
{
    private $methodMatcher;

    private $methodValue;

    public function __construct(\PHPUnit_Framework_MockObject_Matcher_InvokedRecorder $matcher, $value)
    {
        $this->methodMatcher = $matcher;
        $this->methodValue = $value;
    }

    public function getMatcher()
    {
        return $this->methodMatcher;
    }

    public function getValue()
    {
        return $this->methodValue;
    }
}
