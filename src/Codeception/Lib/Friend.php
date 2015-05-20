<?php
namespace Codeception\Lib;
use Codeception\Actor;
use Codeception\Exception\TestRuntime;
use Codeception\SuiteManager;
use Codeception\Lib\Interfaces\MultiSession;

class Friend {

    protected $name;
    protected $actor;
    protected $data = [];
    protected $multiSessionModules = [];

    public function __construct($name, Actor $guy)
    {
        $this->name = $name;
        $this->actor = $guy;
        $this->multiSessionModules = array_filter(SuiteManager::$modules, function($m) {
           return $m instanceof Interfaces\MultiSession;
        });
        if (empty($this->multiSessionModules)) {
            throw new TestRuntime("No multisession modules used. Can't instantiate friend");
        }
    }

    public function does($closure)
    {
        $currentUserData = [];

        foreach ($this->multiSessionModules as $module) {
            $name = $module->_getName();
            $currentUserData[$name] = $module->_backupSessionData();
            if (empty($this->data[$name])) {
                $module->_initializeSession();
                $this->data[$name] = $module->_backupSessionData();
                continue;
            }
            $module->_loadSessionData($this->data[$name]);
        };

        $this->actor->comment(strtoupper("<info>{$this->name} does</info>:"));
        $ret = $closure($this->actor);
        $this->actor->comment(strtoupper("<info>{$this->name} finished</info>"));

        foreach ($this->multiSessionModules as $module) {
            $name = $module->_getName();
            $this->data[$name] = $module->_backupSessionData();
            $module->_loadSessionData($currentUserData[$name]);
        };
        return $ret;
    }

    public function isGoingTo($argumentation)
    {
        $this->actor->amGoingTo($argumentation);
    }

    public function expects($prediction)
    {
        $this->actor->expect($prediction);
    }

    public function expectsTo($prediction)
    {
        $this->actor->expectTo($prediction);
    }

    public function __destruct()
    {
        foreach ($this->multiSessionModules as $module) {
            if (isset($this->data[$module->_getName()])) {
                $module->_closeSession($this->data[$module->_getName()]);
            }
        }
    }

}
 