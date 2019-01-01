<?php
namespace Codeception\Module;

use Codeception\Module as CodeceptionModule;

/**
 * Special module for using asserts in your tests.
 */
class Asserts extends CodeceptionModule
{

    /**
     * Checks that two variables are equal. If you're comparing floating-point values,
     * you can specify the optional "delta" parameter which dictates how great of a precision
     * error are you willing to tolerate in order to consider the two values equal.
     *
     * Regular example:
     * ```php
     * <?php
     * $I->assertEquals($element->getChildrenCount(), 5);
     * ```
     *
     * Floating-point example:
     * ```php
     * <?php
     * $I->assertEquals($calculator->add(0.1, 0.2), 0.3, 'Calculator should add the two numbers correctly.', 0.01);
     * ```
     *
     * @param        $expected
     * @param        $actual
     * @param string $message
     * @param float  $delta
     */
    public function assertEquals($expected, $actual, $message = '', $delta = 0.0)
    {
        parent::assertEquals($expected, $actual, $message, $delta);
    }

    /**
     * Checks that two variables are not equal. If you're comparing floating-point values,
     * you can specify the optional "delta" parameter which dictates how great of a precision
     * error are you willing to tolerate in order to consider the two values not equal.
     *
     * Regular example:
     * ```php
     * <?php
     * $I->assertNotEquals($element->getChildrenCount(), 0);
     * ```
     *
     * Floating-point example:
     * ```php
     * <?php
     * $I->assertNotEquals($calculator->add(0.1, 0.2), 0.4, 'Calculator should add the two numbers correctly.', 0.01);
     * ```
     *
     * @param        $expected
     * @param        $actual
     * @param string $message
     * @param float  $delta
     */
    public function assertNotEquals($expected, $actual, $message = '', $delta = 0.0)
    {
        parent::assertNotEquals($expected, $actual, $message, $delta);
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
     * Checks that a string starts with the given prefix.
     *
     * @param string $prefix
     * @param string $string
     * @param string $message
     */
    public function assertStringStartsWith($prefix, $string, $message = '')
    {
        parent::assertStringStartsWith($prefix, $string, $message);
    }

    /**
     * Checks that a string doesn't start with the given prefix.
     *
     * @param string $prefix
     * @param string $string
     * @param string $message
     */
    public function assertStringStartsNotWith($prefix, $string, $message = '')
    {
        parent::assertStringStartsNotWith($prefix, $string, $message);
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
     * Checks that the condition is NOT true (everything but true)
     *
     * @param        $condition
     * @param string $message
     */
    public function assertNotTrue($condition, $message = '')
    {
        parent::assertNotTrue($condition, $message);
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
     * Checks that the condition is NOT false (everything but false)
     *
     * @param        $condition
     * @param string $message
     */
    public function assertNotFalse($condition, $message = '')
    {
        parent::assertNotFalse($condition, $message);
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
     * @param $expected
     * @param $actual
     * @param $description
     */
    public function assertGreaterOrEquals($expected, $actual, $description = '')
    {
        $this->assertGreaterThanOrEqual($expected, $actual, $description);
    }

    /**
     * @param $expected
     * @param $actual
     * @param $description
     */
    public function assertLessOrEquals($expected, $actual, $description = '')
    {
        $this->assertLessThanOrEqual($expected, $actual, $description);
    }

    /**
     * @param $actual
     * @param $description
     */
    public function assertIsEmpty($actual, $description = '')
    {
        $this->assertEmpty($actual, $description);
    }

    /**
     * @param $key
     * @param $actual
     * @param $description
     */
    public function assertArrayHasKey($key, $actual, $description = '')
    {
        parent::assertArrayHasKey($key, $actual, $description);
    }

    /**
     * @param $key
     * @param $actual
     * @param $description
     */
    public function assertArrayNotHasKey($key, $actual, $description = '')
    {
        parent::assertArrayNotHasKey($key, $actual, $description);
    }

    /**
     * Checks that array contains subset.
     *
     * @param array  $subset
     * @param array  $array
     * @param bool   $strict
     * @param string $message
     */
    public function assertArraySubset($subset, $array, $strict = false, $message = '')
    {
        parent::assertArraySubset($subset, $array, $strict, $message);
    }

    /**
     * @param $expectedCount
     * @param $actual
     * @param $description
     */
    public function assertCount($expectedCount, $actual, $description = '')
    {
        parent::assertCount($expectedCount, $actual, $description);
    }

    /**
     * @param $class
     * @param $actual
     * @param $description
     */
    public function assertInstanceOf($class, $actual, $description = '')
    {
        parent::assertInstanceOf($class, $actual, $description);
    }

    /**
     * @param $class
     * @param $actual
     * @param $description
     */
    public function assertNotInstanceOf($class, $actual, $description = '')
    {
        parent::assertNotInstanceOf($class, $actual, $description);
    }

    /**
     * @param $type
     * @param $actual
     * @param $description
     */
    public function assertInternalType($type, $actual, $description = '')
    {
        parent::assertInternalType($type, $actual, $description);
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

    /**
     * Handles and checks exception called inside callback function.
     * Either exception class name or exception instance should be provided.
     *
     * ```php
     * <?php
     * $I->expectException(MyException::class, function() {
     *     $this->doSomethingBad();
     * });
     *
     * $I->expectException(new MyException(), function() {
     *     $this->doSomethingBad();
     * });
     * ```
     * If you want to check message or exception code, you can pass them with exception instance:
     * ```php
     * <?php
     * // will check that exception MyException is thrown with "Don't do bad things" message
     * $I->expectException(new MyException("Don't do bad things"), function() {
     *     $this->doSomethingBad();
     * });
     * ```
     *
     * @param $exception string or \Exception
     * @param $callback
     *
     * @deprecated use expectThrowable($exception, $callback) instead.
     */
    public function expectException($exception, $callback)
    {
        $this->expectThrowable($exception, $callback);
    }

    /**
     * Handles and checks throwable called inside callback function.
     * Either throwable class name or throwable instance should be provided.
     *
     * ```php
     * <?php
     * $I->expectThrowable(MyException::class, function() {
     *     $this->doSomethingBad();
     * });
     *
     * $I->expectThrowable(new MyException(), function() {
     *     $this->doSomethingBad();
     * });
     * ```
     * If you want to check message or throwable code, you can pass them with throwable instance:
     * ```php
     * <?php
     * // will check that MyException is thrown with "Don't do bad things" message
     * $I->expectThrowable(new MyException("Don't do bad things"), function() {
     *     $this->doSomethingBad();
     * });
     * ```
     *
     * @param $throwable string or \Throwable
     * @param $callback
     */
    public function expectThrowable($throwable, $callback)
    {
        $code = null;
        $msg = null;
        if (is_object($throwable)) {
            $class = get_class($throwable);
            $msg = $throwable->getMessage();
            $code = $throwable->getCode();
        } else {
            $class = $throwable;
        }
        try {
            $callback();
        } catch (\Throwable $t) {
            if (!$t instanceof $class) {
                $this->fail(sprintf("Throwable of class $class expected, but %s caught", get_class($t)));
            }
            if (null !== $msg and $t->getMessage() !== $msg) {
                $this->fail(sprintf(
                    "Throwable of $class expected to be '$msg', but actual message was '%s'",
                    $t->getMessage()
                ));
            }
            if (null !== $code and $t->getCode() !== $code) {
                $this->fail(sprintf(
                    "Throwable of $class expected to have code $code, but actual code was %s",
                    $t->getCode()
                ));
            }
            $this->assertTrue(true); // increment assertion counter
            return;
        }
        $this->fail("Expected throwable of $class, but nothing was caught");
    }
}
