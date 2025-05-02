<?php

declare(strict_types=1);

namespace Codeception\Test;

/**
 * Controls the format returned by a single `#[Examples]` attribute.
 *
 *  • **false** (default value) ⇒ historical behavior:
 *      - A single #[Examples]            ⇒ Example[]   (flat array)
 *      - Multiple #[Examples] / @example ⇒ Example[][]
 *
 *  • **true** ⇒ new unified behavior (always Example[][]).
 *
 * Change the value —for example in tests/_bootstrap.php— like this:
 *
 *      \Codeception\Test\ExampleFormat::$useModern = true;
 */
class ExampleFormat
{
    /** @var bool */
    public static bool $useModern = false;
}
