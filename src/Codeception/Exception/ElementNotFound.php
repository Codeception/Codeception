<?php
namespace Codeception\Exception;

class ElementNotFound extends \PHPUnit_Framework_AssertionFailedError{

    public function __construct($selector, $message = "")
    {
        parent::__construct($message . " '$selector' not found on page.\n'");
    }

}
