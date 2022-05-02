<?php

namespace Codeception\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Incomplete
{
}
