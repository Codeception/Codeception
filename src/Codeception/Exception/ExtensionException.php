<?php

declare(strict_types=1);

namespace Codeception\Exception;

use Exception;

use function is_object;

class ExtensionException extends Exception
{
    /**
     * ExtensionException constructor.
     *
     * @param object|string $extension
     */
    public function __construct($extension, string $message, ?Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
        if (is_object($extension)) {
            $extension = $extension::class;
        }
        $this->message = $extension . "\n\n" . $this->message;
    }
}
