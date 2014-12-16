<?php
namespace Codeception\Exception;

class MalformedLocator extends TestRuntime
{
    public function __construct($locator, $type = "CSS or XPath")
    {
        parent::__construct(ucfirst($type) . " locator is malformed: $locator");
    }
} 