<?php
namespace Codeception\Util\Shared;

trait Asserts
{
    protected function assert($arguments, $not = false)
    {
        $not    = $not ? 'Not' : '';
        $method = ucfirst(array_shift($arguments));
        if (($method === 'True') && $not) {
            $method = 'False';
            $not    = '';
        }
        if (($method === 'False') && $not) {
            $method = 'True';
            $not    = '';
        }

        call_user_func_array(array('\PHPUnit_Framework_Assert', 'assert' . $not . $method), $arguments);
    }

    protected function assertNot($arguments)
    {
        $this->assert($arguments, true);
    }

    /**
     * Checks that two variables are equal.
     *
     * @param        $expected
     * @param        $actual
     * @param string $message
     *
     * @return mixed
     */
    protected function assertEquals($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertEquals($expected, $actual, $message);
    }

    /**
     * Checks that two variables are not equal
     *
     * @param        $expected
     * @param        $actual
     * @param string $message
     */
    protected function assertNotEquals($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertNotEquals($expected, $actual, $message);
    }

    /**
     * Checks that two variables are same
     *
     * @param        $expected
     * @param        $actual
     * @param string $message
     *
     * @return mixed
     */
    protected function assertSame($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertSame($expected, $actual, $message);
    }

    /**
     * Checks that two variables are not same
     *
     * @param        $expected
     * @param        $actual
     * @param string $message
     */
    protected function assertNotSame($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertNotSame($expected, $actual, $message);
    }

    /**
     * Checks that actual is greater than expected
     *
     * @param        $expected
     * @param        $actual
     * @param string $message
     */
    protected function assertGreaterThan($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertGreaterThan($expected, $actual, $message);
    }

    /**
     * @deprecated
     */
    protected function assertGreaterThen($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertGreaterThan($expected, $actual, $message);
    }

    /**
     * Checks that actual is greater or equal than expected
     *
     * @param        $expected
     * @param        $actual
     * @param string $message
     */
    protected function assertGreaterThanOrEqual($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertGreaterThanOrEqual($expected, $actual, $message);
    }

    /**
     * @deprecated
     */
    protected function assertGreaterThenOrEqual($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertGreaterThanOrEqual($expected, $actual, $message);
    }

    /**
     * Checks that actual is less than expected
     *
     * @param        $expected
     * @param        $actual
     * @param string $message
     */
    protected function assertLessThan($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertLessThan($expected, $actual, $message);
    }

    /**
     * Checks that actual is less or equal than expected
     *
     * @param        $expected
     * @param        $actual
     * @param string $message
     */
    protected function assertLessThanOrEqual($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertLessThanOrEqual($expected, $actual, $message);
    }


    /**
     * Checks that haystack contains needle
     *
     * @param        $needle
     * @param        $haystack
     * @param string $message
     */
    protected function assertContains($needle, $haystack, $message = '')
    {
        \PHPUnit_Framework_Assert::assertContains($needle, $haystack, $message);
    }

    /**
     * Checks that haystack doesn't contain needle.
     *
     * @param        $needle
     * @param        $haystack
     * @param string $message
     */
    protected function assertNotContains($needle, $haystack, $message = '')
    {
        \PHPUnit_Framework_Assert::assertNotContains($needle, $haystack, $message);
    }

    /**
     * Checks that variable is empty.
     *
     * @param        $actual
     * @param string $message
     */
    protected function assertEmpty($actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertEmpty($actual, $message);
    }

    /**
     * Checks that variable is not empty.
     *
     * @param        $actual
     * @param string $message
     */
    protected function assertNotEmpty($actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertNotEmpty($actual, $message);
    }

    /**
     * Checks that variable is NULL
     *
     * @param        $actual
     * @param string $message
     */
    protected function assertNull($actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertNull($actual, $message);
    }

    /**
     * Checks that variable is not NULL
     *
     * @param        $actual
     * @param string $message
     */
    protected function assertNotNull($actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertNotNull($actual, $message);
    }

    /**
     * Checks that condition is positive.
     *
     * @param        $condition
     * @param string $message
     */
    protected function assertTrue($condition, $message = '')
    {
        \PHPUnit_Framework_Assert::assertTrue($condition, $message);
    }

    /**
     * Checks that condition is negative.
     *
     * @param        $condition
     * @param string $message
     */
    protected function assertFalse($condition, $message = '')
    {
        \PHPUnit_Framework_Assert::assertFalse($condition, $message);
    }

    protected function assertThat($haystack, $constraint, $message)
    {
        \PHPUnit_Framework_Assert::assertThat($haystack, $constraint, $message);
    }

    protected function assertThatItsNot($haystack, $constraint, $message)
    {
        $constraint = new \PHPUnit_Framework_Constraint_Not($constraint);
        \PHPUnit_Framework_Assert::assertThat($haystack, $constraint, $message);
    }

    /**
     * Fails the test with message.
     *
     * @param $message
     */
    protected function fail($message)
    {
        \PHPUnit_Framework_Assert::fail($message);
    }


}
