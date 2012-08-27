<?php
namespace Codeception\Exception;

class Module extends \Exception {

    protected $module;

    public function __construct($module, $message) {
        $module = ltrim(str_replace('Codeception\Module\\', '', $module), '\\');
        $this->module = $module;
        parent::__construct($message);
        $this->message = '(Exception in '.$module.') ' . $this->message;
    }

}
