<?php

declare(strict_types=1);

namespace Codeception\Command\Shared;

use Codeception\Scenario;

trait ActorTrait
{
    protected function getActorClassName(): ?string
    {
        if (empty($this->settings['actor'])) {
            return null;
        }

        $namespace = "";

        if ($this->settings['namespace']) {
            $namespace .= '\\' . $this->settings['namespace'];
        }

        if (isset($this->settings['support_namespace'])) {
            $namespace .= '\\' . $this->settings['support_namespace'];
        }

        $namespace = rtrim($namespace, '\\') . '\\';

        return $namespace . $this->settings['actor'];
    }

    private function getActor($test)
    {
        $actorClass = $this->getActorClassName();

        return $actorClass ? new $actorClass(new Scenario($test)) : null;
    }
}
