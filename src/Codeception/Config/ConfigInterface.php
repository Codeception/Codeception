<?php

declare(strict_types=1);

namespace Codeception\Config;

interface ConfigInterface
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
