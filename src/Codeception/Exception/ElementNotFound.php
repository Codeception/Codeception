<?php
namespace Codeception\Exception;

use Codeception\Util\Locator;

class ElementNotFound extends \PHPUnit\Framework\AssertionFailedError
{
    public function __construct($selector, $message = null)
    {
        $selector = Locator::humanReadableString($selector);
        parent::__construct($message . " element with $selector was not found.");
    }
}
