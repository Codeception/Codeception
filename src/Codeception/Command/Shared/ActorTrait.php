<?php

declare(strict_types=1);

namespace Codeception\Command\Shared;

trait ActorTrait
{
    protected function getActor(): ?string
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
}
