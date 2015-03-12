<?php

namespace simpleDI {
    use \simpleDIHelpers\NeededHelper as Needed;

    class LoadedTestWithDependencyInjectionCest
    {
        public $a;

        public function __construct($optional = 'abc') {}
        public function _inject(Needed $a) { $this->a = $a; }
        public function testOne() {}
        public function testTwo() {}
    }

    abstract class SkippedAbstractCest
    {
        public function testNothing() {}
    }

    class SkippedWithPrivateConstructorCest
    {
        private function __construct() {}
        public function testNothing() {}
    }

    class AnotherCest
    {
        public function testSome() {}
    }
}

namespace simpleDIHelpers {
    class NeededHelper
    {
        public function _inject(AnotherHelper $a, YetAnotherHelper $b, $optionalParam = 123) {}
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
