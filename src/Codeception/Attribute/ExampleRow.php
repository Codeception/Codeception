<?php

declare(strict_types=1);

namespace Codeception\Attribute;

use Attribute;

/**
 * Describes one example, every attribute adds one row of data:
 *
 * #[ExampleRow('foo', 7)]
 * #[ExampleRow('bar', 8)]
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class ExampleRow
{
    public function __construct(mixed ...$values)
    {
    }
}
