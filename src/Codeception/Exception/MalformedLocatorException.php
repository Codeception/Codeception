<?php
namespace Codeception\Exception;

class MalformedLocatorException extends TestRuntimeException
{
    public function __construct($locator, $type = "CSS or XPath")
    {
        parent::__construct(ucfirst($type) . " locator is malformed: $locator");
    }
}
