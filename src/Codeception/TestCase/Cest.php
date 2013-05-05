<?php
namespace Codeception\TestCase;

use Codeception\Event\Test;

class Cest extends Cept
{
    protected $testClassInstance = null;
    protected $testMethod = null;
    protected $guy = 'CodeGuy';

    public function __construct($dispatcher, array $data = array(), $dataName = '') {
        parent::__construct($dispatcher, $data, $dataName);
        $this->testClassInstance = $data['instance'];
        $this->testMethod = $data['method'];
        $this->guy = $data['guy'];
    }

    public function preload()
    {
        if (file_exists($this->bootstrap)) require $this->bootstrap;
        $I = $this->makeIObject();
        $this->executeTestMethod($I);
        $this->fire('test.parsed', new Test($this));
    }

    public function testCodecept() {
        if (file_exists($this->bootstrap)) require $this->bootstrap;

        $this->scenario->run();
        $I = $this->makeIObject();
        $this->fire('test.before', new Test($this));

        try {
            $this->executeTestMethod($I);
        } catch (\Exception $e) {
            $this->fire('test.after', new Test($this));
            throw $e;
        }
        $this->fire('test.after', new Test($this));
    }

    protected function makeIObject()
    {
        $class_name = '\\'.$this->guy;
        $I = new $class_name($this->scenario);

        if ($spec = $this->getSpecFromMethod()) {
            $I->wantTo($spec);
        }
        // @deprectated. Required only by Unit module
        if (isset($this->testClassInstance->class)) {
            $I->testMethod($this->testClassInstance->class .'.'. $this->testMethod);
        }

        return $I;
    }

    protected function executeTestMethod($I)
    {
        $testMethodSignature = array($this->testClassInstance, $this->testMethod);
        if (!is_callable($testMethodSignature)) throw new \Exception("Method {$this->testMethod} can't be found in tested class");
        call_user_func($testMethodSignature, $I, $this->scenario);
    }

    public function getTestClass()
    {
        return $this->testClassInstance;
    }

    public function getTestMethod()
    {
        return $this->testMethod;
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
        return get_class($this->getTestClass())."::".$this->getTestMethod();
    }

}
