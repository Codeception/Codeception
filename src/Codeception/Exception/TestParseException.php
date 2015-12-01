<?php
namespace Codeception\Exception;

class TestParseException extends \Exception
{
    public function __construct($fileName, $errors = null)
    {
        $this->message = "Couldn't parse test '$fileName'";
        if ($errors) {
            $this->message .= "\n$errors";
        }
    }
}
