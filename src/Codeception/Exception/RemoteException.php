<?php
namespace Codeception\Exception;

class RemoteException extends \Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
        $this->message = "Remote Application Error:\n" . $this->message;
    }
}
