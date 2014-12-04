<?php
namespace Codeception\Exception;

class MalformedLocator extends TestRuntime
{
    public function __construct($type, $locator)
    {
        parent::__construct(ucfirst($type) . " locator is malformed: $locator");
    }
} 