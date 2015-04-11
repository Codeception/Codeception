<?php
namespace Codeception\Exception;

class ElementNotFound extends \PHPUnit_Framework_AssertionFailedError
{

    public function __construct($selector, $message = null)
    {
        if (is_array($selector)) {
            $type = strtolower(key($selector));
            $locator = $selector[$type];
            parent::__construct("Element with $type '$locator' was not found.");
            return;
        }
        if ($selector instanceof \WebDriverBy) {
            $type = $selector->getMechanism();
            $locator = $selector->getValue();
            parent::__construct("Element with $type '$locator' was not found.");
            return;
        }

        parent::__construct($message . " '$selector' was not found.");
    }

}
