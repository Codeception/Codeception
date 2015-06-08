<?php
namespace Codeception\Lib\Actor\Shared;

use Codeception\Scenario;
use Codeception\Lib\Friend as LibFriend;

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
     * @return \Codeception\Lib\Friend
     */
    public function haveFriend($name, $actorClass = null)
    {
        if (!isset($this->friends[$name])) {
            $actor = $actorClass === null ? $this : new $actorClass($this->getScenario());
            $this->friends[$name] = new LibFriend($name, $actor, $this->getScenario()->current('modules'));
        }
        return $this->friends[$name];
    }
}
