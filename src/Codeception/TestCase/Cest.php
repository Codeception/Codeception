<?php
namespace Codeception\TestCase;

class Cest extends \Codeception\TestCase\Cept
{
    protected $testClass = null;
    protected $testMethod = null;
    protected $signature;
    protected $guy = 'CodeGuy';
    protected $dispatcher;
    protected $bootstrap;

    public function __construct($dispatcher, array $data = array(), $dataName = '') {
        parent::__construct($dispatcher, $data, $dataName);
        $this->testClass = $data['class'];
        $this->testMethod = $data['method'];
        $this->static = $data['static'];
        $this->signature = $data['signature'];
        $this->guy = $data['guy'];
    }

    public function testCodecept($run = true) {

        if (file_exists($this->bootstrap)) require $this->bootstrap;

        if (isset($this->testClass->class)) {
            if (!class_exists($this->testClass->class, true)) {
                throw new \Exception("Tested class '{$this->testClass->class}' can't be loaded.");
            }
        }
        // executing test
        $class_name = '\\'.$this->guy;
        $I = new $class_name($this->scenario);
        if ($this->getCoveredMethod()) {
            $I->testMethod($this->signature);
        }

        if ($spec = $this->getSpecFromMethod()) {
            $I->wantTo($spec);
        }

        // preload everything
        $this->executeTestMethod($I);
        $this->dispatcher->dispatch('test.parsed', new \Codeception\Event\Test($this));

        if (!$run) return;
        $this->scenario->run();
        $this->dispatcher->dispatch('test.before', new \Codeception\Event\Test($this));


        if ($this->getCoveredMethod()) {
            $I->testMethod($this->signature);
        }

        try {
            $this->executeTestMethod($I);
        } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->dispatcher->dispatch('test.fail', new \Codeception\Event\Fail($this, $e));
            $this->dispatcher->dispatch('test.after', new \Codeception\Event\Test($this));
            throw $e;
        }
        $this->dispatcher->dispatch('test.after', new \Codeception\Event\Test($this));
    }

    protected function executeTestMethod($I)
    {
        if ($this->static) {
            $class = $this->testClass->class;
            if (!is_callable(array($class, $this->testMethod))) throw new \Exception("Method {$this->specName} can't be found in tested class");
            call_user_func(array(get_class($this->testClass), $this->testMethod), $I, $this->scenario);
        } else {
            if (!is_callable(array($this->testClass, $this->testMethod))) throw new \Exception("Method {$this->specName} can't be found in tested class");
            call_user_func(array($this->testClass, $this->testMethod), $I, $this->scenario);
        }
    }

    public function getTestClass()
    {
        return $this->testClass;
    }

    public function getTestMethod()
    {
        return $this->testMethod;
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

        // search by annotations
        $rm = new \ReflectionMethod($this->testClass, $this->testMethod);
        $doc = $rm->getDocComment();


        if (preg_match('~@(covers|doc) (.*?)\*~si', $doc, $matches)) {
            $method = trim($matches[2]);
            if ($r->hasMethod($method)) return $method;
            return null;
        }

        return null;
    }

    public function getSpecFromMethod() {
        if (strpos(strtolower($this->testMethod),'should') === 0) {
            $text = substr($this->testMethod,6);
            $text = preg_replace('/([A-Z]+)([A-Z][a-z])/', '\\1 \\2', $text);
            $text = preg_replace('/([a-z\d])([A-Z])/', '\\1 \\2', $text);
            $text = strtolower($text);
            return $text;
        }
        return '';
    }

    public function getFileName() {
        return get_class($this)."::".$this->getTestMethod();
    }

}
