<?php

declare(strict_types=1);

namespace Codeception\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Incomplete
{
    public function __construct(string $reason = '')
    {
    }
}
