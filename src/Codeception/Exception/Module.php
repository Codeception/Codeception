<?php
namespace Codeception\Exception;
 
class Module extends \Exception {

    protected $module;
    
    public function __construct($module, $message) {
        $module = str_replace('\Codeception\Module\\','',$module);
        parent::__construct($message);
        $this->message = '(Exception in '.$this->module.') ' . $this->message;
    }

}
