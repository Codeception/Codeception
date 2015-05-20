<?php
/**
 * Also test multiple namespaces/classes per single file.
 */
namespace SimpleA {
    class SimpleTest extends \Codeception\TestCase\Test
    {

        public function testFoo() {
            return true;
        }

        public function testBar() {
            return true;
        }

    }
}

namespace SimpleB {
    class SimpleTest extends \Codeception\TestCase\Test
    {
        public function testBaz() {
            return true;
        }

    }
}

