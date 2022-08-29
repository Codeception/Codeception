<?php

declare(strict_types=1);

namespace Codeception\Exception;

use Exception;

use function is_object;
use function ltrim;
use function str_replace;

class ModuleRequireException extends Exception
{
    /**
     * ModuleRequireException constructor.
     *
     * @param object|string $module
     */
    public function __construct($module, string $message)
    {
        if (is_object($module)) {
            $module = $module::class;
        }
        $module = str_replace('Codeception\\Module\\', '', ltrim($module, '\\'));
        parent::__construct($message);
        $this->message = "[{$module}] module requirements not met --\n \n" . $this->message;
    }
}
