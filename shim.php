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

// Add aliases for PHPUnit 6

namespace {
    if (!class_exists('PHPUnit_Framework_TestCase') && class_exists('PHPUnit\Framework\TestCase')) {
        class_alias('PHPUnit\Framework\Assert', 'PHPUnit_Framework_Assert');
        class_alias('PHPUnit\Framework\AssertionFailedError', 'PHPUnit_Framework_AssertionFailedError');
        class_alias('PHPUnit\Framework\Constraint\Constraint', 'PHPUnit_Framework_Constraint');
        class_alias('PHPUnit\Framework\Constraint\LogicalNot', 'PHPUnit_Framework_Constraint_Not');
        class_alias('PHPUnit\Framework\DataProviderTestSuite', 'PHPUnit_Framework_TestSuite_DataProvider');
        class_alias('PHPUnit\Framework\Exception', 'PHPUnit_Framework_Exception');
        class_alias('PHPUnit\Framework\ExceptionWrapper', 'PHPUnit_Framework_ExceptionWrapper');
        class_alias('PHPUnit\Framework\ExpectationFailedException', 'PHPUnit_Framework_ExpectationFailedException');
        class_alias('PHPUnit\Framework\IncompleteTestError', 'PHPUnit_Framework_IncompleteTestError');
        class_alias('PHPUnit\Framework\SelfDescribing', 'PHPUnit_Framework_SelfDescribing');
        class_alias('PHPUnit\Framework\SkippedTestError', 'PHPUnit_Framework_SkippedTestError');
        class_alias('PHPUnit\Framework\Test', 'PHPUnit_Framework_Test');
        class_alias('PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase');
        class_alias('PHPUnit\Framework\TestFailure', 'PHPUnit_Framework_TestFailure');
        class_alias('PHPUnit\Framework\TestListener', 'PHPUnit_Framework_TestListener');
        class_alias('PHPUnit\Framework\TestResult', 'PHPUnit_Framework_TestResult');
        class_alias('PHPUnit\Framework\TestSuite', 'PHPUnit_Framework_TestSuite');
        class_alias('PHPUnit\Framework\Warning', 'PHPUnit_Framework_Warning');
        class_alias('PHPUnit\Runner\BaseTestRunner', 'PHPUnit_Runner_BaseTestRunner');
        class_alias('PHPUnit\Runner\Filter\Factory', 'PHPUnit_Runner_Filter_Factory');
        class_alias('PHPUnit\Runner\Filter\NameFilterIterator', 'PHPUnit_Runner_Filter_Test');
        class_alias('PHPUnit\Runner\Filter\IncludeGroupFilterIterator', 'PHPUnit_Runner_Filter_Group_Include');
        class_alias('PHPUnit\Runner\Filter\ExcludeGroupFilterIterator', 'PHPUnit_Runner_Filter_Group_Exclude');
        class_alias('PHPUnit\Runner\Version', 'PHPUnit_Runner_Version');
        class_alias('PHPUnit\TextUI\ResultPrinter', 'PHPUnit_TextUI_ResultPrinter');
        class_alias('PHPUnit\TextUI\TestRunner', 'PHPUnit_TextUI_TestRunner');
        class_alias('PHPUnit\Util\Log\JUnit', 'PHPUnit_Util_Log_JUnit');
        class_alias('PHPUnit\Util\Printer', 'PHPUnit_Util_Printer');
        class_alias('PHPUnit\Util\Test', 'PHPUnit_Util_Test');
        class_alias('PHPUnit\Util\TestDox\ResultPrinter', 'PHPUnit_Util_TestDox_ResultPrinter');
        require_once __DIR__ . '/phpunit5-loggers.php'; // TAP and JSON loggers were removed in PHPUnit 6
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
