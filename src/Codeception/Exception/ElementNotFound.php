<?php
namespace Codeception\Exception;

use Codeception\Util\Locator;

class ElementNotFound extends \PHPUnit\Framework\AssertionFailedError
{
    public function __construct($selector, $message = null)
    {
        if (!is_string($selector) || strpos($selector, "'") === false) {
            $selector = Locator::humanReadableString($selector);
        }
        parent::__construct($message . " element with $selector was not found.");
    }
}
