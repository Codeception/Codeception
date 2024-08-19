<?php

declare(strict_types=1);

namespace Codeception\Attribute;

use Attribute;

#[Attribute]
class Identifier
{
    public function __construct(protected string $id)
    {
    }

    public function getId(): string
    {
        return $this->id;
    }
}
