<?php
/**
 * Also test multiple namespaces/classes per single file.
 */
namespace SimpleA {
    class SimpleTest extends \Codeception\Test\TestCase
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
    class SimpleTest extends \Codeception\Test\TestCase
    {
        public function testBaz() {
            return true;
        }

    }
}

