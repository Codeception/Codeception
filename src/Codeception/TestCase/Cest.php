<?php
namespace Codeception\TestCase;

class Cest extends \Codeception\TestCase
{
    protected $testClass;
    protected $signature;

    public function __construct($dispatcher, array $data = array(), $dataName = '') {
        parent::__construct($dispatcher, $data, $dataName);
        $this->testClass = $data['class'];
        $this->testMethod = $data['method'];
        $this->static = $data['static'];
        $this->signature = $data['signature'];

        if (!isset($this->testClass->class)) {
            throw new \Exception("Cest {$data['name']} has no binding to tested class. Please, provide public property class with the name of class being tested.");
        }
    }
    
    public function loadScenario() {

        $unit = $this->testClass;

        if (!class_exists($this->testClass->class, true)) {
            throw new \Exception("Tested class '{$unit->class}' can't be loaded.");
        }

        // executing test
        $I = new \CodeGuy($this->scenario);
        $I->testMethod($this->signature);

        if ($this->static) {
            $class = $unit->class;
            if (!is_callable(array($class, $this->testMethod))) throw new \Exception("Method {$this->specName} can't be found in tested class");
            call_user_func(array(get_class($unit), $this->testMethod), $I);
        } else {
            if (!is_callable(array($unit, $this->testMethod))) throw new \Exception("Method {$this->specName} can't be found in tested class");
            call_user_func(array($this->testClass, $this->testMethod), $I);
        }
    }

    public function getTestClass()
    {
        return $this->testClass;
    }

}
