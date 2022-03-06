<?php

/**
 * Also test multiple namespaces/classes per single file.
 */

namespace SimpleA {
    class SimpleTest extends \Codeception\Test\Unit
    {
        public function testFoo(): bool
        {
            return true;
        }

        public function testBar(): bool
        {
            return true;
        }
    }
}

namespace SimpleB {
    class SimpleTest extends \Codeception\Test\Unit
    {
        public function testBaz(): bool
        {
            return true;
        }
    }
}
