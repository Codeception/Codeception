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

namespace Codeception\TestCase {

    class Test extends \Codeception\Test\Unit {
    }
}

namespace Codeception\Module {

    class Symfony2 extends Symfony {
    }

    class Phalcon1 extends Phalcon {
    }

    class Phalcon2 extends Phalcon {
    }
}

namespace Codeception\Platform {
    abstract class Group extends \Codeception\GroupObject
    {
    }
    abstract class Extension extends \Codeception\Extension
    {
    }
}
namespace {
    class_alias('Codeception\TestInterface', 'Codeception\TestCase');

    // phpunit codecoverage updates
    if (class_exists('SebastianBergmann\CodeCoverage\CodeCoverage')) {
        class_alias('SebastianBergmann\CodeCoverage\CodeCoverage', 'PHP_CodeCoverage');
        class_alias('SebastianBergmann\CodeCoverage\Report\Text', 'PHP_CodeCoverage_Report_Text');
        class_alias('SebastianBergmann\CodeCoverage\Report\PHP', 'PHP_CodeCoverage_Report_PHP');
        class_alias('SebastianBergmann\CodeCoverage\Report\Clover', 'PHP_CodeCoverage_Report_Clover');
        class_alias('SebastianBergmann\CodeCoverage\Report\Crap4j', 'PHP_CodeCoverage_Report_Crap4j');
        class_alias('SebastianBergmann\CodeCoverage\Report\Html\Facade', 'PHP_CodeCoverage_Report_HTML');
        class_alias('SebastianBergmann\CodeCoverage\Exception', 'PHP_CodeCoverage_Exception');
    }
}

// @codingStandardsIgnoreEnd
