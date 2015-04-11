<?php
namespace Codeception\Exception;

class ModuleRequireException extends \Exception
{

    public function __construct($module, $message)
    {
        if (is_object($module)) {
            $module = get_class($module);
        }
        $module = str_replace('Codeception\Module\\', '', ltrim($module, '\\'));
        parent::__construct($message);
        $this->message = $module . " module requirements are not met!\n" . $this->message;
    }

}
