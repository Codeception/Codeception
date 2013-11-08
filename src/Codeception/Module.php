<?php
namespace Codeception;

use Codeception\Exception\ModuleConfig;
use Codeception\Util\Debug;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Module
{
    /**
     * By setting it to false module wan't inherit methods of parent class.
     *
     * @var bool
     */
    public static $includeInheritedActions = true;

    /**
     * Allows to explicitly set what methods have this class.
     *
     * @var array
     */
    public static $onlyActions = array();

    /**
     * Allows to explicitly exclude actions from module.
     *
     * @var array
     */
    public static $excludeActions = array();

    /**
     * Allows to rename actions
     *
     * @var array
     */
    public static $aliases = array();

    protected $debugStack = array();

    protected $storage = array();

    protected $config = array();

    protected $backupConfig = array();

    protected $requiredFields = array();

    private $debugOutput;

    public function __construct($config = null)
    {
        $this->backupConfig = $this->config;
        if (is_array($config)) {
            $this->_setConfig($config);
        }
    }


    
    public function _setConfig($config)
    {
        $this->config = $this->backupConfig = array_merge($this->config, $config);
        $this->validateConfig();
    }

    public function _reconfigure($config)
    {
        $this->config =  array_merge($this->backupConfig, $config);
        $this->validateConfig();        
    }

    public function _resetConfig()
    {
        $this->config = $this->backupConfig;
    }

    protected function validateConfig()
    {
        $fields = array_keys($this->config);
        if (array_intersect($this->requiredFields, $fields) != $this->requiredFields)
            throw new ModuleConfig($this->getName(),"
                Options: ".implode(', ', $this->requiredFields)." are required\n
                Update configuration and set all required fields\n\n
        ");
    }

    public function getName()
    {
        $module = get_class($this);
         if (preg_match('@\\\\([\w]+)$@', $module, $matches)) {
             $module = $matches[1];
         }
         return $module;
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

    // HOOK: before each suite
    public function _beforeSuite($settings = array())
    {
    }

    // HOOK: after suite
    public function _afterSuite()
    {
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
        Debug::debug($message);
    }

    protected function debugSection($title, $message) {
        $this->debug("[$title] $message");
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

        call_user_func_array(array('\PHPUnit_Framework_Assert', 'assert'.$not.$method), $arguments);
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
     * Checks that variable is not NULL
     *
     * @param $actual
     * @param string $message
     */
    protected function assertNotNull($actual, $message = '')
    {
        return \PHPUnit_Framework_Assert::assertNotNull($actual, $message);
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
        if (!$this->hasModule($name)) throw new \Codeception\Exception\Module($this->getName(), "Module $name couldn't be connected");
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
