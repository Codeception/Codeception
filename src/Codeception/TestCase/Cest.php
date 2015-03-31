<?php

namespace Codeception\TestCase;

use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Util\Annotation;

class Cest extends \Codeception\TestCase implements
    Interfaces\ScenarioDriven,
    Interfaces\Descriptive,
    Interfaces\Reported,
    Interfaces\Configurable
{
    use Shared\Actor;
    use Shared\Dependencies;
    use Shared\ScenarioPrint;

    protected $testClassInstance = null;
    protected $testMethod = null;

    public function __construct(array $data = [], $dataName = '')
    {
        parent::__construct('testCodecept', $data, $dataName);
    }

    public function getName($withDataSet = true)
    {
        return $this->testMethod;
    }

    public function preload()
    {
        $this->scenario->setFeature($this->getSpecFromMethod());
        $code = $this->getRawBody();
        $this->parser->parseFeature($code);
        $this->parser->parseScenarioOptions($code, 'scenario');
        $this->di->injectDependencies($this->testClassInstance);
        $this->fire(Events::TEST_PARSED, new TestEvent($this));
    }

    public function getRawBody()
    {
        $method = new \ReflectionMethod($this->testClassInstance, $this->testMethod);
        $start_line = $method->getStartLine() - 1; // it's actually - 1, otherwise you wont get the function() block
        $end_line = $method->getEndLine();
        $source = file($method->getFileName());
        return implode("", array_slice($source, $start_line, $end_line - $start_line));
    }

    public function testCodecept()
    {
        $this->fire(Events::TEST_BEFORE, new TestEvent($this));

        $this->scenario->stopIfBlocked();
        $I = $this->makeIObject();

        try {
            $this->executeBefore($this->testMethod, $I);
            $this->executeTestMethod($I);
            $this->executeAfter($this->testMethod, $I);
        } catch (\Exception $e) {
            if (is_callable([$this->testClassInstance, '_failed'])) {
                $this->testClassInstance->_failed($I);
            }
            // fails and errors are now handled by Codeception\PHPUnit\Listener
            throw $e;
        }

        $this->fire(Events::TEST_AFTER, new TestEvent($this));
    }

    protected function executeBefore($testMethod, $I)
    {
        if (is_callable([$this->testClassInstance, '_before'])) {
            $this->testClassInstance->_before($I);
        }

        $annotations = \PHPUnit_Util_Test::parseTestMethodAnnotations(get_class($this->testClassInstance), $testMethod);
        if (!empty($annotations['method']['before'])) {
            foreach ($annotations['method']['before'] as $m) {
                $this->executeContextMethod(trim($m), $I);
            }
        }
    }

    protected function executeAfter($testMethod, $I)
    {
        $annotations = \PHPUnit_Util_Test::parseTestMethodAnnotations(get_class($this->testClassInstance), $testMethod);
        if (!empty($annotations['method']['after'])) {
            foreach ($annotations['method']['after'] as $m) {
                $this->executeContextMethod(trim($m), $I);
            }
        }

        if (is_callable([$this->testClassInstance, '_after'])) {
            $this->testClassInstance->_after($I);
        }
    }

    protected function executeContextMethod($context, $I)
    {
        if (method_exists($this->testClassInstance, $context)) {
            $this->executeBefore($context, $I);
            $this->invoke($context, [$I, $this->scenario]);
            $this->executeAfter($context, $I);
            return;
        }

        throw new \LogicException(
            "Method $context defined in annotation but does not exists in " . get_class($this->testClassInstance)
        );
    }

    protected function makeIObject()
    {
        $className = '\\' . $this->actor;
        $I = new $className($this->scenario);
        $spec = $this->getSpecFromMethod();

        if ($spec) {
            $I->wantTo($spec);
        }
        return $I;
    }

    protected function invoke($methodName, array $context)
    {
        foreach ($context as $class) {
            $this->di->set($class);
        }
        $this->di->injectDependencies($this->testClassInstance, $methodName, $context);
    }

    protected function executeTestMethod($I)
    {
        $testMethodSignature = [$this->testClassInstance, $this->testMethod];
        if (!is_callable($testMethodSignature)) {
            throw new \Exception("Method {$this->testMethod} can't be found in tested class");
        }
        $this->invoke($this->testMethod, [$I, $this->scenario]);
    }

    public function getTestClass()
    {
        return $this->testClassInstance;
    }

    public function getTestMethod()
    {
        return $this->testMethod;
    }

    public function getSpecFromMethod()
    {
        $text = $this->testMethod;
        $text = preg_replace('/([A-Z]+)([A-Z][a-z])/', '\\1 \\2', $text);
        $text = preg_replace('/([a-z\d])([A-Z])/', '\\1 \\2', $text);
        $text = strtolower($text);
        return $text;
    }

    public function configActor($actor)
    {
        foreach (['actor', 'guy'] as $annotation) {
            $definedActor = Annotation::forMethod($this->testClassInstance, $this->testMethod)->fetch($annotation);
            if (!$definedActor) {
                $definedActor = Annotation::forClass($this->testClassInstance)->fetch($annotation);
            }
            if ($definedActor) {
                $this->actor = $definedActor;
                return $this;
            }
        }

        $this->actor = $actor;
        return $this;
    }

    public function getSignature()
    {
        return get_class($this->getTestClass()) . "::" . $this->getTestMethod();
    }

    public function getFileName()
    {
        return $this->testFile;
    }

    public function toString()
    {
        return $this->getFeature() . " (" . $this->getSignature() . ")";
    }

    public function getEnvironment()
    {
        return Annotation::forMethod($this->testClassInstance, $this->testMethod)->fetchAll('env');
    }

    /**
     * @return array
     */
    public function getReportFields()
    {
        return [
            'file'    => $this->getFileName(),
            'name'    => $this->getTestMethod(),
            'class'   => get_class($this->getTestClass()),
            'feature' => $this->getFeature()
        ];
    }

}
