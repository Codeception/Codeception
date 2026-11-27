<?php
// phpcs:ignoreFile
namespace Tests\Unit;

use JetBrains\PhpStorm\Immutable;

class ReadonlyAnonymousClassCest
{
    public function tryToTest()
    {
        $class1 = new readonly class (12) {
            public function __construct(public mixed $value)
            {
            }
        };

        $class2 = new readonly class(12) {
            public function __construct(public mixed $value)
            {
            }
        };

        $class3 = new #[Immutable] readonly class {
        };

        $class4 = new readonly class extends \ArrayObject {
        };
    }
}
