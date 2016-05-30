<?php
namespace Codeception\Lib;

use Codeception\Actor;
use Codeception\Exception\TestRuntimeException;

class Friend
{
    protected $name;
    protected $actor;
    protected $data = [];
    protected $multiSessionModules = [];

    public function __construct($name, Actor $actor, $modules = [])
    {
        $this->name = $name;
        $this->actor = $actor;

        $this->multiSessionModules = array_filter($modules, function ($m) {
            return $m instanceof Interfaces\MultiSession;
        });

        if (empty($this->multiSessionModules)) {
            throw new TestRuntimeException("No multisession modules used. Can't instantiate friend");
        }
    }

    public function does($closure)
    {
        $currentUserData = [];

        foreach ($this->multiSessionModules as $module) {
            $name = $module->_getName();
            $currentUserData[$name] = $module->_backupSession();
            if (empty($this->data[$name])) {
                $module->_initializeSession();
                $this->data[$name] = $module->_backupSession();
                continue;
            }
            $module->_loadSession($this->data[$name]);
        };

        $this->actor->comment(strtoupper("<info>{$this->name} does --- </info>"));
        $ret = $closure($this->actor);
        $this->actor->comment(strtoupper("<info>--- {$this->name} finished</info>"));

        foreach ($this->multiSessionModules as $module) {
            $name = $module->_getName();
            $this->data[$name] = $module->_backupSession();
            $module->_loadSession($currentUserData[$name]);
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

    public function leave()
    {
        foreach ($this->multiSessionModules as $module) {
            if (isset($this->data[$module->_getName()])) {
                $module->_closeSession($this->data[$module->_getName()]);
            }
        }
    }
}
