<?php

namespace Codeception\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS |  Attribute::IS_REPEATABLE)]
final class Group
{
}
