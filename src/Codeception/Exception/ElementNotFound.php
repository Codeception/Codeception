<?php

declare(strict_types=1);

namespace Codeception\Exception;

use Codeception\Util\Locator;
use PHPUnit\Framework\AssertionFailedError;
use function is_string;
use function strpos;

class ElementNotFound extends AssertionFailedError
{
    public function __construct($selector, string $message = '')
    {
        if (!is_string($selector) || strpos($selector, "'") === false) {
            $selector = Locator::humanReadableString($selector);
        }
        parent::__construct("{$message} element with {$selector} was not found.");
    }
}
