<?php
namespace Codeception\TestCase;

class Cest extends \Codeception\TestCase
{
    protected $testClass;
    
    public function __construct($name, array $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $this->testClass = $data['class'];
        $this->testMethod = $data['method'];
        $this->static = $data['static'];

        if (!isset($this->testClass->class)) {
            throw new \Exception("Cest $name has no binding to tested class. Please, provide public property class with the name of class being tested.");
        }
    }
    
    public function loadScenario() {

        $unit = $this->testClass;

        if (!class_exists($this->testClass->class)) {
            throw new \Exception("Tested class in {$unit->class} can't be loaded.");
        }

        if (method_exists($unit, '_beforeEach')) {
            call_user_func(array($unit,'_beforeEach'));
        }

        // executing test
        $I = new \CodeGuy($this->scenario);
        $I->testMethod($this->specName);

        if ($this->static) {
            $class = $unit->class;
            if (!is_callable(array($class, $this->testMethod))) throw new \Exception("Method {$this->specName} can't be found in tested class");
            call_user_func(array(get_class($unit), $this->testMethod), $I);
        } else {
            if (!is_callable(array($unit, $this->testMethod))) throw new \Exception("Method {$this->specName} can't be found in tested class");
            call_user_func(array($this->testClass, $this->testMethod), $I);
        }
    }
}
