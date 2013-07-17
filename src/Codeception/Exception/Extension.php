<?php
namespace Codeception\Exception;

class Extension extends \Exception {
    public function __construct($extension, $message, \Exception $previous = null) {
        parent::__construct($message, $previous);
        $this->message = $extension."\n\n". $this->message;
    }
}