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
        $code = null;
        $msg = null;
        if (is_object($exception)) {
            /** @var $exception \Exception  **/
             $class = get_class($exception);
            $msg = $exception->getMessage();
            $code = $exception->getCode();
        } else {
            $class = $exception;
        }
        try {
            $callback();
        } catch (\Exception $e) {
            if (!$e instanceof $class) {
                $this->fail(sprintf("Exception of class $class expected to be thrown, but %s caught", get_class($e)));
            }
            if (null !== $msg and $e->getMessage() !== $msg) {
                $this->fail(sprintf(
                    "Exception of $class expected to be '$msg', but actual message was '%s'",
                    $e->getMessage()
                ));
            }
            if (null !== $code and $e->getCode() !== $code) {
                $this->fail(sprintf(
                    "Exception of $class expected to have code $code, but actual code was %s",
                    $e->getCode()
                ));
            }
            $this->assertTrue(true); // increment assertion counter
             return;
        }
        $this->fail("Expected exception to be thrown, but nothing was caught");
    }
}
