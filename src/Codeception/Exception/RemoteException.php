<?php

declare(strict_types=1);

namespace Codeception\Exception;

use Exception;

class RemoteException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
        $this->message = "Remote Application Error:\n" . $this->message;
    }
}
