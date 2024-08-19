<?php

declare(strict_types=1);

namespace Codeception\Attribute;

use Attribute;

#[Attribute]
class Key
{
    public function __construct(protected string $key)
    {
    }

    public function getKey(): string
    {
        return $this->key;
    }
}
