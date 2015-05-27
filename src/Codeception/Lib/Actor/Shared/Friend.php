<?php
namespace Codeception\Lib\Actor\Shared;

use Codeception\Scenario;

trait Friend
{
    protected $friends = [];

    /**
     * @return Scenario
     */
    abstract protected function getScenario();

    /**
     * @param $name
     * @param $actorClass
     * @return Friend
     */
    public function haveFriend($name, $actorClass = null)
    {
        if (!isset($this->friends[$name])) {
            $actor = $actorClass === null ? $this : new $actorClass($this->getScenario());
            $this->friends[$name] = new \Codeception\Lib\Friend($name, $actor);
        }
        return $this->friends[$name];
    }

}