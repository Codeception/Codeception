<?php
namespace Codeception\Module;

use Codeception\Module as CodeceptionModule;
use \Codeception\Util\Shared\Asserts as SharedAsserts;

/**
 * Special module for using asserts in your tests.
 */
class Asserts extends CodeceptionModule
{
    use SharedAsserts;

    /**
     * Checks that two variables are equal.
     *
     * @param        $expected
     * @param        $actual
     * @param string $message
     */
    public function assertEquals($expected, $actual, $message = '')
    {
        parent::assertEquals($expected, $actual, $message);
    }

    /**
     * Checks that two variables are not equal
     *
     * @param        $expected
     * @param        $actual
     * @param string $message
     */
    public function assertNotEquals($expected, $actual, $message = '')
    {
        parent::assertNotEquals($expected, $actual, $message);
    }

    /**
     * Checks that two variables are same
     *
     * @param        $expected
     * @param        $actual
     * @param string $message
     */
    public function assertSame($expected, $actual, $message = '')
    {
        parent::assertSame($expected, $actual, $message);
    }

    /**
     * Checks that two variables are not same
     *
     * @param        $expected
     * @param        $actual
     * @param string $message
     */
    public function assertNotSame($expected, $actual, $message = '')
    {
        parent::assertNotSame($expected, $actual, $message);
    }

    /**
     * Checks that actual is greater than expected
     *
     * @param        $expected
     * @param        $actual
     * @param string $message
     */
    public function assertGreaterThan($expected, $actual, $message = '')
    {
        parent::assertGreaterThan($expected, $actual, $message);
    }

    /**
     * @deprecated
     */
    public function assertGreaterThen($expected, $actual, $message = '')
    {
        parent::assertGreaterThan($expected, $actual, $message);
    }

    /**
     * Checks that actual is greater or equal than expected
     *
     * @param        $expected
     * @param        $actual
     * @param string $message
     */
    public function assertGreaterThanOrEqual($expected, $actual, $message = '')
    {
        parent::assertGreaterThanOrEqual($expected, $actual, $message);
    }

    /**
     * @deprecated
     */
    public function assertGreaterThenOrEqual($expected, $actual, $message = '')
    {
        parent::assertGreaterThanOrEqual($expected, $actual, $message);
    }

    /**
     * Checks that actual is less than expected
     *
     * @param        $expected
     * @param        $actual
     * @param string $message
     */
    public function assertLessThan($expected, $actual, $message = '')
    {
        parent::assertLessThan($expected, $actual, $message);
    }

    /**
     * Checks that actual is less or equal than expected
     *
     * @param        $expected
     * @param        $actual
     * @param string $message
     */
    public function assertLessThanOrEqual($expected, $actual, $message = '')
    {
        parent::assertLessThanOrEqual($expected, $actual, $message);
    }


    /**
     * Checks that haystack contains needle
     *
     * @param        $needle
     * @param        $haystack
     * @param string $message
     */
    public function assertContains($needle, $haystack, $message = '')
    {
        parent::assertContains($needle, $haystack, $message);
    }

    /**
     * Checks that haystack doesn't contain needle.
     *
     * @param        $needle
     * @param        $haystack
     * @param string $message
     */
    public function assertNotContains($needle, $haystack, $message = '')
    {
        parent::assertNotContains($needle, $haystack, $message);
    }

    /**
     * Checks that string match with pattern
     *
     * @param string $pattern
     * @param string $string
     * @param string $message
     */
    public function assertRegExp($pattern, $string, $message = '')
    {
        parent::assertRegExp($pattern, $string, $message);
    }

    /**
     * Checks that string not match with pattern
     *
     * @param string $pattern
     * @param string $string
     * @param string $message
     */
    public function assertNotRegExp($pattern, $string, $message = '')
    {
        parent::assertNotRegExp($pattern, $string, $message);
    }


    /**
     * Checks that variable is empty.
     *
     * @param        $actual
     * @param string $message
     */
    public function assertEmpty($actual, $message = '')
    {
        parent::assertEmpty($actual, $message);
    }

    /**
     * Checks that variable is not empty.
     *
     * @param        $actual
     * @param string $message
     */
    public function assertNotEmpty($actual, $message = '')
    {
        parent::assertNotEmpty($actual, $message);
    }

    /**
     * Checks that variable is NULL
     *
     * @param        $actual
     * @param string $message
     */
    public function assertNull($actual, $message = '')
    {
        parent::assertNull($actual, $message);
    }

    /**
     * Checks that variable is not NULL
     *
     * @param        $actual
     * @param string $message
     */
    public function assertNotNull($actual, $message = '')
    {
        parent::assertNotNull($actual, $message);
    }

    /**
     * Checks that condition is positive.
     *
     * @param        $condition
     * @param string $message
     */
    public function assertTrue($condition, $message = '')
    {
        parent::assertTrue($condition, $message);
    }

    /**
     * Checks that condition is negative.
     *
     * @param        $condition
     * @param string $message
     */
    public function assertFalse($condition, $message = '')
    {
        parent::assertFalse($condition, $message);
    }

    /**
     * Checks if file exists
     *
     * @param string $filename
     * @param string $message
     */
    public function assertFileExists($filename, $message = '')
    {
        parent::assertFileExists($filename, $message);
    }
    
    /**
     * Checks if file doesn't exist
     *
     * @param string $filename
     * @param string $message
     */
    public function assertFileNotExists($filename, $message = '')
    {
        parent::assertFileNotExists($filename, $message);
    }

    /**
     * Fails the test with message.
     *
     * @param $message
     */
    public function fail($message)
    {
        parent::fail($message);
    }
}
