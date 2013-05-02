<?php
namespace Codeception\Exception;

class ModuleConfig extends \Exception {

    public function __construct($module, $message) {
        $module = str_replace('Codeception\Module\\', '', ltrim($module, '\\'));
        parent::__construct($message);
        $this->message = $module." module is not configured!\n ". $this->message;
    }


}
