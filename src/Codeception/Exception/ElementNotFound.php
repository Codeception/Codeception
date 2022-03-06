<?php

declare(strict_types=1);

namespace Codeception\Exception;

use Codeception\Util\Locator;
use PHPUnit\Framework\AssertionFailedError;

use function is_string;

class ElementNotFound extends AssertionFailedError
{
    public function __construct($selector, string $message = '')
    {
        if (!is_string($selector) || !str_contains($selector, "'")) {
            $selector = Locator::humanReadableString($selector);
        }
        parent::__construct("{$message} element with {$selector} was not found.");
    }
}
