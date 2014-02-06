<?php
namespace Codeception\Lib;
use Codeception\AbstractGuy;
use Codeception\Exception\TestRuntime;
use Codeception\SuiteManager;
use Codeception\Lib\MultiSessionInterface;

class Friend {

    protected $name;
    protected $guy;
    protected $data = [];
    protected $multiSessionModules = [];

    public function __construct($name, AbstractGuy $guy)
    {
        $this->name = $name;
        $this->guy = $guy;
        $this->multiSessionModules = array_filter(SuiteManager::$modules, function($m) {
           return $m instanceof MultiSessionInterface;
        });
        if (empty($this->multiSessionModules)) {
            throw new TestRuntime("No multisession modules used. Can't instantiate friend");
        }
    }

    public function does($closure)
    {
        $currentUserData = [];

        foreach ($this->multiSessionModules as $module) {
            $currentUserData[$module->getName()] = $module->_backupSessionData();
            if (empty($this->data)) {
                $module->_initializeSession();
                $this->data[$module->getName()] = $module->_backupSessionData();
                continue;
            }
            $module->_loadSessionData($this->data[$module->getName()]);
        };

        $this->guy->comment(strtoupper("<info>{$this->name} does</info>:"));
        $ret = $closure($this->guy);
        $this->guy->comment(strtoupper("<info>{$this->name} finished</info>"));

        foreach ($this->multiSessionModules as $module) {
            $this->data[$module->getName()] = $module->_backupSessionData();
            $module->_loadSessionData($currentUserData[$module->getName()]);
        };
        return $ret;
    }

    public function amGoingTo($argumentation)
    {
        $this->guy->amGoingTo($argumentation);
    }

    public function expect($prediction)
    {
        $this->guy->expect($prediction);
    }

    public function expectTo($prediction)
    {
        $this->guy->expectTo($prediction);
    }

    public function __destruct()
    {
        foreach ($this->multiSessionModules as $module) {
            $module->_closeSession($this->data[$module->getName()]);
        }
    }

}
 