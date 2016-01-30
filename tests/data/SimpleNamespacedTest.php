<?php
/**
 * Also test multiple namespaces/classes per single file.
 */
namespace SimpleA {
    class SimpleTest extends \Codeception\Test\Unit
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
    class SimpleTest extends \Codeception\Test\Unit
    {
        public function testBaz() {
            return true;
        }

    }
}

