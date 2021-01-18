<?php

declare(strict_types=1);

namespace Codeception\Exception;

use function ucfirst;

class MalformedLocatorException extends TestRuntimeException
{
    public function __construct(string $locator, string $type = 'CSS or XPath')
    {
        parent::__construct(ucfirst($type) . " locator is malformed: {$locator}");
    }
}
