<?php

declare(strict_types=1);

namespace Codeception\Lib\Actor\Shared;

use Codeception\Lib\Friend as LibFriend;
use Codeception\Scenario;

trait Friend
{
    protected array $friends = [];

    abstract protected function getScenario(): Scenario;

    public function haveFriend(string $name, string $actorClass = null): LibFriend
    {
        if (!isset($this->friends[$name])) {
            $actor = $actorClass === null ? $this : new $actorClass($this->getScenario());
            $this->friends[$name] = new LibFriend($name, $actor, $this->getScenario()->current('modules'));
        }
        return $this->friends[$name];
    }
}
