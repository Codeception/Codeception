<?php
namespace Codeception;

abstract class Module {

	protected $debugStack = array();

	protected $storage = array();
    
    protected $config = array();

    protected $requiredFields = array();

    public function _setConfig($config)
    {
        $this->config = $config;
        $fields = array_keys($this->config);
        if (array_intersect($this->requiredFields, $fields) != $this->requiredFields)
            throw new \Codeception\Exception\ModuleConfig(get_class($this),"
                Options: ".implode(', ', $this->requiredFields)." are required\n
                Update cunfiguration and set all required fields\n\n
        ");
    }
	
	public function _hasRequiredFields()
	{
		return !empty($this->requiredFields);
	}

    // HOOK: used after configuration is loaded
    public function _initialize() {}

	// HOOK: on every Guy class initialization
	public function _cleanup()
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

    public function getModule($module) {
        $module = '\Codeception\Module\\'.$module;
        if (!isset(SuiteManager::$modules[$module])) throw new \Codeception\Exception\Module($module, 'module not found');
        return SuiteManager::$modules[$module];
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

		call_user_func_array(array('\PHPUnit_Framework_Assert', 'assert'.$not.$method), $arguments);
	}

	protected function assertNot($arguments) {
		$this->assert($arguments, true);
	}

}
