<?php
namespace Codeception\Module;

// here you can define custom functions for CodeGuy 

class CodeHelper extends \Codeception\Module\Unit
{
    public function haveFakeModule($module) {
        $this->haveFakeClass($module);
        \Codeception\SuiteManager::addModule(get_class($module));
        \Codeception\SuiteManager::initializeModules();
    }

}
