<?php

namespace Codeception\Exception;

class ModuleConfig extends \Exception
{
    public function __construct($module, $message, \Exception $previous = null)
    {
        $module = str_replace('Codeception\Module\\', '', ltrim($module, '\\'));
        parent::__construct($message, 0, $previous);
        $this->message = $module . " module is not configured!\n " . $this->message;
    }
}
