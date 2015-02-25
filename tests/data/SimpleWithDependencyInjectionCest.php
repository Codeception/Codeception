<?php

namespace simpleDI {
    use \simpleDIHelpers\NeededHelper as Needed;

    class LoadedTestWithDependencyInjection
    {
        public function __construct($optional = 'abc') {}
        public function _inject(Needed $a) {}
        public function testOne() {}
        public function testTwo() {}
    }

    abstract class SkippedAbstractTest
    {
        public function testNothing() {}
    }

    class SkippedWithPrivateConstructorTest
    {
        private function __construct() {}
        public function testNothing() {}
    }
}

namespace simpleDIHelpers {
    class NeededHelper
    {
        public function _inject(AnotherHelper $a, YetAnotherHelper $b, $optionalParam = 123) {}
        public function testSome() {}
    }

    class AnotherHelper
    {
        public function __construct() {}
    }

    class YetAnotherHelper
    {
        public function __construct() {}
    }
}
