<?php
namespace Codeception;

abstract class AbstractGuy  {
    public static $methods = array();

    /**
     * @var \Codeception\Scenario
     */
    protected $scenario;

    public function __construct(\Codeception\Scenario $scenario) {
        $this->scenario = $scenario;

	    foreach (\Codeception\SuiteManager::$modules as $module) {
			$module->_cleanup();
	    }
    }

	public function wantToTest($text) {
		$this->scenario->setFeature(strtolower("test $text"));
	}

	public function wantTo($text) {
		$this->scenario->setFeature(strtolower($text));
	}

    public function amTestingClass($text) {
        $this->scenario->setFeature($text);
    }

    public function amTestingMethod($method) {
        $this->testMethod($method);
    }

    public function testMethod($signature) {
        if (!$this->scenario->getFeature()) {
            $this->scenario->setFeature("execute method $signature()");
        } else {
            $this->scenario->setFeature($this->scenario->getFeature() . " with [[$signature]]");
        }
        $this->scenario->when(array_merge(array('testMethod', $signature)));
    }

	public function expectTo($prediction) {
		$this->scenario->comment(array('expect to '.$prediction));
	}

	public function amGoingTo($argumentation) {
	    $this->scenario->comment(array('am going to '.$argumentation));
	}

    public function __call($method, $args) {
        // if (!in_array($method, array_keys(TestGuy::$methods))) throw new \RuntimeException("Action $method not defined");
//        foreach ($args as $k => $arg) {
//            if (is_object($arg)) $args[$k] = clone($arg);
//        }
        if (0 === strpos($method,'see')) {
            $this->scenario->then(array_merge(array($method) ,$args));
        } elseif (0 === strpos($method,'am')) {
            $this->scenario->given(array_merge(array($method) ,$args));
        } else {
            $this->scenario->when(array_merge(array($method),$args));
        }
    }

}
