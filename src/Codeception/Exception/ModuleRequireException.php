<?php

declare(strict_types=1);

namespace Codeception\Exception;

use Exception;
use function get_class;
use function is_object;
use function ltrim;
use function str_replace;

class ModuleRequireException extends Exception
{
    public function __construct($module, $message)
    {
        if (is_object($module)) {
            $module = get_class($module);
        }
        $module = str_replace('Codeception\\Module\\', '', ltrim($module, '\\'));
        parent::__construct($message);
        $this->message = "[$module] module requirements not met --\n \n" . $this->message;
    }
}
