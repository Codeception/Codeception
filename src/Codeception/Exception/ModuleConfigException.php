<?php

declare(strict_types=1);

namespace Codeception\Exception;

use Exception;
use function get_class;
use function is_object;
use function ltrim;
use function str_replace;

class ModuleConfigException extends Exception
{
    public function __construct($module, $message, \Exception $previous = null)
    {
        if (is_object($module)) {
            $module = get_class($module);
        }
        $module = str_replace('Codeception\Module\\', '', ltrim($module, '\\'));
        parent::__construct($message, 0, $previous);
        $this->message = $module . " module is not configured!\n \n" . $this->message;
    }
}
