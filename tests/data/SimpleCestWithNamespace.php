<?php
/**
 * Also test multiple namespaces/classes per single file.
 */
namespace SimpleA {
    class SimpleTest
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
    class SimpleTest
    {
        public function testBaz() {
            return true;
        }

    }
}

