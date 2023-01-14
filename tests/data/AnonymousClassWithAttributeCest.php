<?php
// phpcs:ignoreFile
namespace Tests\Unit;

use JetBrains\PhpStorm\Immutable;
use Tests\Support\UnitTester;

class AnonymousClassWithAttributeCest
{
    public function tryToTest(UnitTester $I)
    {
        $class1 = new #[Immutable] class{
            public function foo(): string
            {
                return 'test';
            }
        };

        $class2 = new #[Immutable] class {
            public function foo(): string
            {
                return 'test';
            }
        };

        $class3 = new #[Immutable] class
        {
            public function foo(): string
            {
                return 'test';
            }
        };
    }
}
