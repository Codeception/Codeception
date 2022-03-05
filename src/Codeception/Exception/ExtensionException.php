<?php
namespace Codeception\Exception;

class ExtensionException extends \Exception
{
    public function __construct($extension, $message, \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
        if (is_object($extension)) {
            $extension = get_class($extension);
        }
        $this->message = $extension . "\n\n" . $this->message;
    }
}
