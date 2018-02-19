<?php
// @codingStandardsIgnoreStart
// Add aliases for PHPUnit 6
namespace {
    if (!class_exists('PHPUnit\Framework\Assert') && class_exists('PHPUnit_Framework_Assert')) {
        class_alias('PHPUnit_Framework_Assert', 'PHPUnit\Framework\Assert');
    }

    // load PHPUnit 4.8 classes avoiding its so-called compatibility layer
    if (class_exists('PHPUnit_Framework_TestCase') && !class_exists('PHPUnit\Framework\TestCase', false)) {
        class_alias('PHPUnit_Framework_AssertionFailedError', 'PHPUnit\Framework\AssertionFailedError');
        class_alias('PHPUnit_Framework_Test', 'PHPUnit\Framework\Test');
        class_alias('PHPUnit_Framework_TestCase', 'PHPUnit\Framework\TestCase');
        class_alias('PHPUnit_Runner_BaseTestRunner', 'PHPUnit\Runner\BaseTestRunner');
        class_alias('PHPUnit_Framework_TestListener', 'PHPUnit\Framework\TestListener');
        class_alias('PHPUnit_Framework_TestSuite', 'PHPUnit\Framework\TestSuite');
        class_alias('PHPUnit_Framework_Constraint', 'PHPUnit\Framework\Constraint\Constraint');
        class_alias('PHPUnit_Framework_Constraint_Not', 'PHPUnit\Framework\Constraint\LogicalNot');
        class_alias('PHPUnit_Framework_TestSuite_DataProvider', 'PHPUnit\Framework\DataProviderTestSuite');
        class_alias('PHPUnit_Framework_Exception', 'PHPUnit\Framework\Exception');
        class_alias('PHPUnit_Framework_ExceptionWrapper', 'PHPUnit\Framework\ExceptionWrapper');
        class_alias('PHPUnit_Framework_ExpectationFailedException', 'PHPUnit\Framework\ExpectationFailedException');
        class_alias('PHPUnit_Framework_IncompleteTestError', 'PHPUnit\Framework\IncompleteTestError');
        class_alias('PHPUnit_Framework_SelfDescribing', 'PHPUnit\Framework\SelfDescribing');
        class_alias('PHPUnit_Framework_SkippedTestError', 'PHPUnit\Framework\SkippedTestError');
        class_alias('PHPUnit_Framework_TestFailure', 'PHPUnit\Framework\TestFailure');
        class_alias('PHPUnit_Framework_TestResult', 'PHPUnit\Framework\TestResult');
        class_alias('PHPUnit_Framework_Warning', 'PHPUnit\Framework\Warning');
        class_alias('PHPUnit_Runner_Filter_Factory', 'PHPUnit\Runner\Filter\Factory');
        class_alias('PHPUnit_Runner_Filter_Test', 'PHPUnit\Runner\Filter\NameFilterIterator');
        class_alias('PHPUnit_Runner_Filter_Group_Include', 'PHPUnit\Runner\Filter\IncludeGroupFilterIterator');
        class_alias('PHPUnit_Runner_Filter_Group_Exclude', 'PHPUnit\Runner\Filter\ExcludeGroupFilterIterator');
        class_alias('PHPUnit_Runner_Version', 'PHPUnit\Runner\Version');
        class_alias('PHPUnit_TextUI_ResultPrinter', 'PHPUnit\TextUI\ResultPrinter');
        class_alias('PHPUnit_TextUI_TestRunner', 'PHPUnit\TextUI\TestRunner');
        class_alias('PHPUnit_Util_Log_JUnit', 'PHPUnit\Util\Log\JUnit');
        class_alias('PHPUnit_Util_Printer', 'PHPUnit\Util\Printer');
        class_alias('PHPUnit_Util_Test', 'PHPUnit\Util\Test');
        class_alias('PHPUnit_Util_TestDox_ResultPrinter', 'PHPUnit\Util\TestDox\ResultPrinter');

    }
    if (!class_exists('\PHPUnit\Util\Log\JSON') || !class_exists('\PHPUnit\Util\Log\TAP')) {
        if (class_exists('PHPUnit\Util\Printer')) {
            require_once __DIR__ . '/phpunit5-loggers.php'; // TAP and JSON loggers were removed in PHPUnit 6
        }
    }

    // phpunit codecoverage updates
    if (!class_exists('SebastianBergmann\CodeCoverage\CodeCoverage') && class_exists('PHP_CodeCoverage')) {
        class_alias('PHP_CodeCoverage', 'SebastianBergmann\CodeCoverage\CodeCoverage');
        class_alias('PHP_CodeCoverage_Report_Text', 'SebastianBergmann\CodeCoverage\Report\Text');
        class_alias('PHP_CodeCoverage_Report_PHP', 'SebastianBergmann\CodeCoverage\Report\PHP');
        class_alias('PHP_CodeCoverage_Report_Clover', 'SebastianBergmann\CodeCoverage\Report\Clover');
        class_alias('PHP_CodeCoverage_Report_Crap4j', 'SebastianBergmann\CodeCoverage\Report\Crap4j');
        class_alias('PHP_CodeCoverage_Report_HTML', 'SebastianBergmann\CodeCoverage\Report\Html\Facade');
        class_alias('PHP_CodeCoverage_Report_XML', 'SebastianBergmann\CodeCoverage\Report\Xml\Facade');
        class_alias('PHP_CodeCoverage_Exception', 'SebastianBergmann\CodeCoverage\Exception');
    }

    if (class_exists('PHP_Timer') && !class_exists('SebastianBergmann\Timer\Timer')) {
        class_alias('PHP_Timer', 'SebastianBergmann\Timer\Timer');
    }

    if (!class_exists('\PHPUnit\Framework\Constraint\LogicalNot') && class_exists('\PHPUnit\Framework\Constraint\Not')) {
        class_alias('\PHPUnit\Framework\Constraint\Not', '\PHPUnit\Framework\Constraint\LogicalNot');
    }
}

// @codingStandardsIgnoreEnd
