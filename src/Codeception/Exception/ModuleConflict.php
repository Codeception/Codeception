<?php
namespace Codeception\Exception;

class ModuleConflict extends \Exception
{
    public function __construct($module, $conflicted, $message) {
        if (is_object($module)) {
            $module = get_class($module);
        }
        $module = ltrim(str_replace('Codeception\Module\\', '', $module), '\\');
        parent::__construct($message);
        $this->message = "$module module conflicts with $conflicted!\n\n" . $this->message;
    }
}
