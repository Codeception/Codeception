<?php
// @codingStandardsIgnoreStart
// Add aliases for PHPUnit 6
namespace {    

    if (!class_exists('PHPUnit_Framework_Assert')) {
        class_alias('PHPUnit\Framework\Assert', 'PHPUnit_Framework_Assert');
    }
    
    if (!class_exists('PHPUnit_Framework_TestCase')) {
        class_alias('PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase');
    }

    if (!class_exists('PHPUnit\Util\Log\JSON') || !class_exists('PHPUnit\Util\Log\TAP')) {
        if (class_exists('PHPUnit\Util\Printer')) {
            require_once __DIR__ . '/phpunit5-loggers.php'; // TAP and JSON loggers were removed in PHPUnit 6
        }
    }
}

// @codingStandardsIgnoreEnd
