<?php
namespace Codeception\TestCase;

class Cest extends \Codeception\TestCase
{
    protected $testClass = null;
    protected $testMethod = null;
    protected $signature;

    public function __construct($dispatcher, array $data = array(), $dataName = '') {
        parent::__construct($dispatcher, $data, $dataName);
        $this->testClass = $data['class'];
        $this->testMethod = $data['method'];
        $this->static = $data['static'];
        $this->signature = $data['signature'];
    }
    
    public function loadScenario() {
        if (file_exists($this->bootstrap)) require $this->bootstrap;

        $unit = $this->testClass;

        if (isset($this->testClass->class)) {
            if (!class_exists($this->testClass->class, true)) {
                throw new \Exception("Tested class '{$unit->class}' can't be loaded.");
            }
        }

        // executing test
        $I = new \CodeGuy($this->scenario);
        if ($this->getCoveredMethod()) {
            $I->testMethod($this->signature);
        }

        if ($spec = $this->getSpecFromMethod()) {
            $I->wantTo($spec);
        }

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

    public function getCoveredClass()
    {
        $class = $this->getTestClass();
        if (isset($class->class)) return $class->class;
        return null;
    }

    public function getCoveredMethod()
    {
        if (!$this->getCoveredClass()) return null;
        $r = new \ReflectionClass($this->getCoveredClass());
        if ($r->hasMethod($this->testMethod)) return $this->testMethod;
        return null;
    }

    public function getSpecFromMethod() {
        if (strpos(strtolower($this->testMethod),'should') === 0) {
            $text = substr($this->testMethod,6);
            $text = preg_replace('/([A-Z]+)([A-Z][a-z])/', '\\1 \\2', $text);
            $text = preg_replace('/([a-z\d])([A-Z])/', '\\1 \\2', $text);
            return $text;
        }
        return '';
    }

}
