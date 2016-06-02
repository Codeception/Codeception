<?php
namespace Codeception\Util\Shared;

trait Asserts
{
    protected function assert($arguments, $not = false)
    {
        $not = $not ? 'Not' : '';
        $method = ucfirst(array_shift($arguments));
        if (($method === 'True') && $not) {
            $method = 'False';
            $not = '';
        }
        if (($method === 'False') && $not) {
            $method = 'True';
            $not = '';
        }

        call_user_func_array(['\PHPUnit_Framework_Assert', 'assert' . $not . $method], $arguments);
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
     * Checks that string match with pattern
     *
     * @param string $pattern
     * @param string $string
     * @param string $message
     */
    protected function assertRegExp($pattern, $string, $message = '')
    {
        \PHPUnit_Framework_Assert::assertRegExp($pattern, $string, $message);
    }
    
    /**
     * Checks that string not match with pattern
     *
     * @param string $pattern
     * @param string $string
     * @param string $message
     */
    protected function assertNotRegExp($pattern, $string, $message = '')
    {
        \PHPUnit_Framework_Assert::assertNotRegExp($pattern, $string, $message);
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

    /**
     *
     * @param        $haystack
     * @param        $constraint
     * @param string $message
     */
    protected function assertThat($haystack, $constraint, $message)
    {
        \PHPUnit_Framework_Assert::assertThat($haystack, $constraint, $message);
    }

    /**
     * Checks that haystack doesn't attend
     *
     * @param        $haystack
     * @param        $constraint
     * @param string $message
     */
    protected function assertThatItsNot($haystack, $constraint, $message)
    {
        $constraint = new \PHPUnit_Framework_Constraint_Not($constraint);
        \PHPUnit_Framework_Assert::assertThat($haystack, $constraint, $message);
    }

    
    /**
     * Checks if file exists
     *
     * @param string $filename
     * @param string $message
     */
    protected function assertFileExists($filename, $message = '')
    {
        \PHPUnit_Framework_Assert::assertFileExists($filename, $message);
    }
    
        
    /**
     * Checks if file doesn't exist
     *
     * @param string $filename
     * @param string $message
     */
    protected function assertFileNotExists($filename, $message = '')
    {
        \PHPUnit_Framework_Assert::assertFileNotExists($filename, $message);
    }

    /**
     * @param $expected
     * @param $actual
     * @param $description
     */
    protected function assertGreaterOrEquals($expected, $actual, $description)
    {
        \PHPUnit_Framework_Assert::assertGreaterThanOrEqual($expected, $actual, $description);
    }

    /**
     * @param $expected
     * @param $actual
     * @param $description
     */
    protected function assertLessOrEquals($expected, $actual, $description)
    {
        \PHPUnit_Framework_Assert::assertLessThanOrEqual($expected, $actual, $description);
    }

    /**
     * @param $actual
     * @param $description
     */
    protected function assertIsEmpty($actual, $description)
    {
        \PHPUnit_Framework_Assert::assertEmpty($actual, $description);
    }

    /**
     * @param $key
     * @param $actual
     * @param $description
     */
    protected function assertArrayHasKey($key, $actual, $description)
    {
        \PHPUnit_Framework_Assert::assertArrayHasKey($key, $actual, $description);
    }

    /**
     * @param $key
     * @param $actual
     * @param $description
     */
    protected function assertArrayNotHasKey($key, $actual, $description)
    {
        \PHPUnit_Framework_Assert::assertArrayNotHasKey($key, $actual, $description);
    }

    /**
     * @param $class
     * @param $actual
     * @param $description
     */
    protected function assertInstanceOf($class, $actual, $description)
    {
        \PHPUnit_Framework_Assert::assertInstanceOf($class, $actual, $description);
    }

    /**
     * @param $class
     * @param $actual
     * @param $description
     */
    protected function assertNotInstanceOf($class, $actual, $description)
    {
        \PHPUnit_Framework_Assert::assertNotInstanceOf($class, $actual, $description);
    }

    /**
     * @param $type
     * @param $actual
     * @param $description
     */
    protected function assertInternalType($type, $actual, $description)
    {
        \PHPUnit_Framework_Assert::assertInternalType($type, $actual, $description);
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
