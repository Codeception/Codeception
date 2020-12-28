<?php
// @codingStandardsIgnoreStart
// PHPUnit 6 compatibility

namespace PHPUnit\Framework {
    if (!interface_exists(Test::class, false)) {
        interface Test extends \Countable {
            public function run(TestResult $result = null);
        }

    }
    if (!interface_exists(SelfDescribing::class, false)) {
        interface SelfDescribing
        {
            public function toString();
        }
    }

}
// @codingStandardsIgnoreEnd
