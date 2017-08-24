<?php
namespace Codeception\Exception;

class TestParseException extends \Exception
{
    public function __construct($fileName, $errors = null, $line = null)
    {
        if ($line) {
            $this->message = "Couldn't parse test '$fileName' on line $line";
        } else {
            $this->message = "Couldn't parse test '$fileName'";
        }
        if ($errors) {
            $this->message .= "\n$errors";
        }
    }
}
