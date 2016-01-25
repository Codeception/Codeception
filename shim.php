<?php
// @codingStandardsIgnoreStart
namespace Symfony\Component\CssSelector {
if (!class_exists('Symfony\Component\CssSelector\CssSelectorConverter')) {
        class CssSelectorConverter {
            function toXPath($cssExpr, $prefix = 'descendant-or-self::') {
                return CssSelector::toXPath($cssExpr, $prefix);
            }
        }
    }
}

// prefering old names
namespace Codeception {

    interface TestCase extends \Codeception\TestInterface {
    }
}

namespace Codeception\TestCase {

    class Test extends \Codeception\Test\Unit {
    }
}
// @codingStandardsIgnoreEnd
