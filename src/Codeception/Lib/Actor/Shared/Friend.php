<?php
namespace Codeception\Lib\Actor\Shared;

trait Friend
{
    protected $friends = [];
    /**
     * @param $name
     * @param $actorClass
     * @return Friend
     */
    public function haveFriend($name, $actorClass = null)
    {
        if (!isset($this->friends[$name])) {
            $guy = $actorClass === null ? $this : new $actorClass($this->scenario);
            $this->friends[$name] = new \Codeception\Lib\Friend($name, $guy);
        }
        return $this->friends[$name];
    }

}