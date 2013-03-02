<?php
namespace Codeception;

abstract class Module {

	protected $debugStack = array();

	protected $storage = array();

    protected $config = array();

    protected $requiredFields = array();

    public function _setConfig($config)
    {
        $this->config = array_merge($this->config, $config);
        $fields = array_keys($this->config);
        if (array_intersect($this->requiredFields, $fields) != $this->requiredFields)
            throw new \Codeception\Exception\ModuleConfig(get_class($this),"
                Options: ".implode(', ', $this->requiredFields)." are required\n
                Update configuration and set all required fields\n\n
        ");
    }

	public function _hasRequiredFields()
	{
		return !empty($this->requiredFields);
	}

    // HOOK: used after configuration is loaded
    public function _initialize() {
    }

	// HOOK: on every Guy class initialization
	public function _cleanup() {
	}

	// HOOK: before every step
	public function _beforeStep(\Codeception\Step $step) {
	}

	// HOOK: after every  step
	public function _afterStep(\Codeception\Step $step) {
	}

	// HOOK: before scenario
	public function _before(\Codeception\TestCase $test) {
	}

	// HOOK: after scenario
	public function _after(\Codeception\TestCase $test) {
	}

	// HOOK: on fail
	public function _failed(\Codeception\TestCase $test, $fail) {
	}


	protected function debug($message) {
	    $this->debugStack[] = $message;
	}

	protected function debugSection($title, $message) {
		$this->debug("[$title] $message");
	}

	public function _clearDebugOutput() {
		$this->debugStack = array();
	}

	public function _getDebugOutput() {
		$debugStack = $this->debugStack;
		$this->_clearDebugOutput();
	    return $debugStack;
	}

	protected function assert($arguments, $not = false) {
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

		call_user_func_array(array('\Codeception\PHPUnit\Assert', 'assert'.$not.$method), $arguments);
	}

    /**
     * Checks that two variables are equal.
     *
     * @param $expected
     * @param $actual
     * @param string $message
     * @return mixed
     */
    protected function assertEquals($expected, $actual, $message = '')
    {
        return \PHPUnit_Framework_Assert::assertEquals($expected, $actual, $message);
    }

    /**
     * Checks that a Web page contains text (ignoring HTML tags)
     *
     * @param $needle
     * @param $haystack
     * @param string $message
     */
    protected function assertPageContains($needle, $haystack, $message = '')
    {
        return \Codeception\PHPUnit\Assert::assertPageContains($needle, $haystack, $message);
    }

    /**
     * Checks that a web page doesn't contain text (ignoring HTML tags)
     *
     * @param $needle
     * @param $haystack
     * @param string $message
     * @return mixed
     */
    protected function assertPageNotContains($needle, $haystack, $message = '')
    {
        return \Codeception\PHPUnit\Assert::assertPageNotContains($needle, $haystack, $message);
    }

    /**
     * Checks that two variables are not equal
     *
     * @param $expected
     * @param $actual
     * @param string $message
     */
    protected function assertNotEquals($expected, $actual, $message = '')
    {
        return \PHPUnit_Framework_Assert::assertNotEquals($expected, $actual, $message);
    }

    /**
     * Checks that expected is greater then actual
     *
     * @param $expected
     * @param $actual
     * @param string $message
     */
    protected function assertGreaterThen($expected, $actual, $message = '')
    {
        return \PHPUnit_Framework_Assert::assertGreaterThan($expected, $actual, $message);
    }

    /**
     * Checks that expected is greater or equal then actual
     *
     * @param $expected
     * @param $actual
     * @param string $message
     */
    protected function assertGreaterThenOrEqual($expected, $actual, $message = '')
    {
        return \PHPUnit_Framework_Assert::assertGreaterThanOrEqual($expected, $actual, $message);
    }

    /**
     * Checks that expected is lower then actual
     *
     * @param $expected
     * @param $actual
     * @param string $message
     * @return mixed
     */
    protected function assertLowerThen($expected, $actual, $message = '')
    {
        return \PHPUnit_Framework_Assert::assertLowerThan($expected, $actual, $message);
    }

    /**
     * Checks that expected is lower or equal then actual
     *
     * @param $expected
     * @param $actual
     * @param string $message
     * @return mixed
     */
    protected function assertLowerThenOrEqual($expected, $actual, $message = '')
    {
        return \PHPUnit_Framework_Assert::assertLowerThanOrEqual($expected, $actual, $message);
    }

    /**
     * Checks that haystack contains needle
     *
     * @param $needle
     * @param $haystack
     * @param string $message
     */
    protected function assertContains($needle, $haystack, $message = '')
    {
        return \PHPUnit_Framework_Assert::assertContains($needle, $haystack, $message);
    }

    /**
     * Checks that haystack doesn't contain needle.
     *
     * @param $needle
     * @param $haystack
     * @param string $message
     */
    protected function assertNotContains($needle, $haystack, $message = '')
    {
        return \PHPUnit_Framework_Assert::assertNotContains($needle, $haystack, $message);
    }

    /**
     * Checks that variable is empty.
     *
     * @param $actual
     * @param string $message
     */
    protected function assertEmpty($actual, $message = '')
    {
        return \PHPUnit_Framework_Assert::assertEmpty($actual, $message);
    }

    /**
     * Checks that variable is not empty.
     *
     * @param $actual
     * @param string $message
     */
    protected function assertNotEmpty($actual, $message = '')
    {
        return \PHPUnit_Framework_Assert::assertNotEmpty($actual, $message);
    }

    /**
     * Checks that variable is NULL
     *
     * @param $actual
     * @param string $message
     */
    protected function assertNull($actual, $message = '')
    {
        return \PHPUnit_Framework_Assert::assertNull($actual, $message);
    }

    /**
     * Checks that condition is positive.
     *
     * @param $condition
     * @param string $message
     */
    protected function assertTrue($condition, $message = '')
    {
        return \PHPUnit_Framework_Assert::assertTrue($condition, $message);
    }

    /**
     * Checks that condition is negative.
     *
     * @param $condition
     * @param string $message
     */
    protected function assertFalse($condition, $message = '')
    {
        return \PHPUnit_Framework_Assert::assertFalse($condition, $message);
    }

    /**
     * Fails the test with message.
     *
     * @param $message
     */
    protected function fail($message)
    {
        return \PHPUnit_Framework_Assert::fail($message);
    }

	protected function assertNot($arguments) {
		$this->assert($arguments, true);
	}

    protected function hasModule($name)
    {
        return isset(\Codeception\SuiteManager::$modules[$name]);
    }

    protected function getModules()
    {
        return \Codeception\SuiteManager::$modules;
    }

    protected function getModule($name) {
        if (!$this->hasModule($name)) throw new \Codeception\Exception\Module(__CLASS__, "Module $name couldn't be connected");
        return \Codeception\SuiteManager::$modules[$name];
    }

    protected function scalarizeArray($array)
    {
        foreach ($array as $k => $v) {
            if (! is_scalar($v)) {
                $array[$k] = (is_array($v) || $v instanceof \ArrayAccess) ? $this->scalarizeArray($v) : (string)$v;
            }
        }

        return $array;
    }
}
