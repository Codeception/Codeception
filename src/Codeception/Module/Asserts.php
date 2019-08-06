<?php
namespace Codeception\Module;

use Codeception\Module as CodeceptionModule;
use Codeception\Util\Shared\Asserts as SharedAsserts;

/**
 * Special module for using asserts in your tests.
 */
class Asserts extends CodeceptionModule
{
    use SharedAsserts {
        assertEquals as public;
        assertNotEquals as public;
        assertSame as public;
        assertNotSame as public;
        assertGreaterThan as public;
        assertGreaterThanOrEqual as public;
        assertLessThan as public;
        assertLessThanOrEqual as public;
        assertContains as public;
        assertNotContains as public;
        assertRegExp as public;
        assertNotRegExp as public;
        assertEmpty as public;
        assertNotEmpty as public;
        assertNull as public;
        assertNotNull as public;
        assertTrue as public;
        assertFalse as public;
        assertFileExists as public;
        assertFileNotExists as public;
        assertGreaterOrEquals  as public;
        assertLessOrEquals  as public;
        assertIsEmpty  as public;
        assertArrayHasKey  as public;
        assertArrayNotHasKey  as public;
        assertInstanceOf  as public;
        assertNotInstanceOf  as public;
        assertInternalType  as public;
        assertCount  as public;
        assertStringStartsWith  as public;
        assertStringStartsNotWith  as public;
        assertNotTrue  as public;
        assertNotFalse  as public;
        assertStringContainsString  as public;
        assertStringContainsStringIgnoringCase  as public;
        assertStringNotContainsString  as public;
        assertStringNotContainsStringIgnoringCase  as public;
        assertIsArray  as public;
        assertIsBool  as public;
        assertIsFloat  as public;
        assertIsInt  as public;
        assertIsNumeric  as public;
        assertIsObject  as public;
        assertIsResource  as public;
        assertIsString  as public;
        assertIsScalar  as public;
        assertIsCallable  as public;
        assertIsNotArray  as public;
        assertIsNotBool  as public;
        assertIsNotFloat  as public;
        assertIsNotInt  as public;
        assertIsNotNumeric  as public;
        assertIsNotObject  as public;
        assertIsNotResource  as public;
        assertIsNotString  as public;
        assertIsNotScalar  as public;
        assertIsNotCallable  as public;
        fail as public;
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
     */
    public function expectException($exception, $callback)
    {
        $this->expectThrowable($exception, $callback);
    }

    /**
     * Handles and checks throwables (Exceptions/Errors) called inside the callback function.
     * Either throwable class name or throwable instance should be provided.
     *
     * ```php
     * <?php
     * $I->expectThrowable(MyThrowable::class, function() {
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
     * // will check that throwable MyError is thrown with "Don't do bad things" message
     * $I->expectThrowable(new MyError("Don't do bad things"), function() {
     *     $this->doSomethingBad();
     * });
     * ```
     *
     * @param $throwable string or \Throwable
     * @param $callback
     */
    public function expectThrowable($throwable, $callback)
    {
        if (is_object($throwable)) {
            /** @var $throwable \Throwable */
            $class = get_class($throwable);
            $msg = $throwable->getMessage();
            $code = $throwable->getCode();
        } else {
            $class= $throwable;
            $msg = null;
            $code = null;
        }

        try {
            $callback();
        } catch (\Exception $t) {
            $this->checkThrowable($t, $class, $msg, $code);

            return;
        } catch (\Throwable $t) {
            $this->checkThrowable($t, $class, $msg, $code);

            return;
        }

        $this->fail("Expected throwable of class '$class' to be thrown, but nothing was caught");
    }

    /**
     * Check if the given throwable matches the expected data,
     * fail (throws an exception) if it does not.
     *
     * @param \Throwable $throwable
     * @param string $expectedClass
     * @param string $expectedMsg
     * @param int $expectedCode
     */
    protected function checkThrowable($throwable, $expectedClass, $expectedMsg, $expectedCode)
    {
        if (!($throwable instanceof $expectedClass)) {
            $this->fail(sprintf(
                "Exception of class '$expectedClass' expected to be thrown, but class '%s' was caught",
                get_class($throwable)
            ));
        }

        if (null !== $expectedMsg && $throwable->getMessage() !== $expectedMsg) {
            $this->fail(sprintf(
                "Exception of class '$expectedClass' expected to have message '$expectedMsg', but actual message was '%s'",
                $throwable->getMessage()
            ));
        }

        if (null !== $expectedCode && $throwable->getCode() !== $expectedCode) {
            $this->fail(sprintf(
                "Exception of class '$expectedClass' expected to have code '$expectedCode', but actual code was '%s'",
                $throwable->getCode()
            ));
        }

        $this->assertTrue(true); // increment assertion counter
    }
}
