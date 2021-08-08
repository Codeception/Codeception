<?php

declare(strict_types=1);

namespace Codeception\Lib;

use Codeception\Actor;
use Codeception\Exception\TestRuntimeException;
use Codeception\Lib\Interfaces\MultiSession;

class Friend
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var Actor
     */
    protected $actor;
    /**
     * @var array
     */
    protected $data = [];
    /**
     * @var array|null
     */
    protected $multiSessionModules = [];

    public function __construct(string $name, Actor $actor, array $modules = [])
    {
        $this->name = $name;
        $this->actor = $actor;

        $this->multiSessionModules = array_filter($modules, function ($m) {
            return $m instanceof MultiSession;
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
        }

        $this->actor->comment(strtoupper("{$this->name} does ---"));
        $ret = $closure($this->actor);
        $this->actor->comment(strtoupper("--- {$this->name} finished"));

        foreach ($this->multiSessionModules as $module) {
            $name = $module->_getName();
            $this->data[$name] = $module->_backupSession();
            $module->_loadSession($currentUserData[$name]);
        }
        return $ret;
    }

    public function isGoingTo(string $argumentation): void
    {
        $this->actor->amGoingTo($argumentation);
    }

    public function expects(string $prediction): void
    {
        $this->actor->expect($prediction);
    }

    public function expectsTo(string $prediction): void
    {
        $this->actor->expectTo($prediction);
    }

    public function leave(): void
    {
        foreach ($this->multiSessionModules as $module) {
            if (isset($this->data[$module->_getName()])) {
                $module->_closeSession($this->data[$module->_getName()]);
            }
        }
    }
}
