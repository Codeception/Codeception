<?php
namespace Codeception\Exception;

class ModuleRequire extends \Exception {

    public function __construct($module, $message) {
        $module = str_replace('Codeception\Module\\', '', ltrim($module, '\\'));
        parent::__construct($message);
        $this->message = $module." module requirements are not met!\n ". $this->message;
    }

}
