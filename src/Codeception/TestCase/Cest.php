<?php
namespace Codeception\TestCase;

use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\TestCase;
use Codeception\TestCase\Interfaces\Configurable;
use Codeception\TestCase\Interfaces\Descriptive;
use Codeception\TestCase\Interfaces\ScenarioDriven;
use Codeception\TestCase\Shared\Actor;
use Codeception\TestCase\Shared\Dependencies;
use Codeception\TestCase\Shared\ScenarioPrint;
use Codeception\Util\Annotation;

class Cest extends \Codeception\Lib\Test implements
    TestCase,
    ScenarioDriven,
    Configurable,
    Descriptive
{
    use Actor;
    use ScenarioPrint;
    use Dependencies;

    protected $testClassInstance;
    protected $testMethod;

    public function test()
    {
        $I = $this->makeIObject();

        $this->prepareActorForTest();
        try {
            $this->executeHook($I, 'before');
            $this->executeBeforeMethods($this->testMethod, $I);
            $this->executeTestMethod($I);
            $this->executeAfterMethods($this->testMethod, $I);
            $this->executeHook($I, 'after');
        } catch (\Exception $e) {
            $this->executeHook($I, 'failed');
            // fails and errors are now handled by Codeception\PHPUnit\Listener
            throw $e;
        }
    }

    public function preload()
    {
        $this->scenario->setFeature($this->getSpecFromMethod());
        $code = $this->getRawBody();
        $this->parser->parseFeature($code);
        $this->parser->attachMetadata(Annotation::forMethod($this->testClassInstance, $this->testMethod)->raw());
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


    public function getSpecFromMethod()
    {
        $text = $this->testMethod;
        $text = preg_replace('/([A-Z]+)([A-Z][a-z])/', '\\1 \\2', $text);
        $text = preg_replace('/([a-z\d])([A-Z])/', '\\1 \\2', $text);
        $text = strtolower($text);
        return $text;
    }


    protected function executeBeforeMethods($testMethod, $I)
    {
        $annotations = \PHPUnit_Util_Test::parseTestMethodAnnotations(get_class($this->testClassInstance), $testMethod);
        if (!empty($annotations['method']['before'])) {
            foreach ($annotations['method']['before'] as $m) {
                $this->executeContextMethod(trim($m), $I);
            }
        }
    }

    protected function executeAfterMethods($testMethod, $I)
    {
        $annotations = \PHPUnit_Util_Test::parseTestMethodAnnotations(get_class($this->testClassInstance), $testMethod);
        if (!empty($annotations['method']['after'])) {
            foreach ($annotations['method']['after'] as $m) {
                $this->executeContextMethod(trim($m), $I);
            }
        }
    }

    protected function executeContextMethod($context, $I)
    {
        if (method_exists($this->testClassInstance, $context)) {
            $this->executeBeforeMethods($context, $I);
            $this->invoke($context, [$I, $this->scenario]);
            $this->executeAfterMethods($context, $I);
            return;
        }

        throw new \LogicException(
            "Method $context defined in annotation but does not exist in " . get_class($this->testClassInstance)
        );
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
        if (! is_callable($testMethodSignature)) {
            throw new \Exception("Method {$this->testMethod} can't be found in tested class");
        }
        $this->invoke($this->testMethod, [$I, $this->scenario]);
    }

    public function configActor($actor)
    {
        foreach (['actor', 'guy'] as $annotation) {
            $definedActor = Annotation::forMethod($this->testClassInstance, $this->testMethod)->fetch($annotation);
            if ($definedActor === null) {
                $definedActor = Annotation::forClass($this->testClassInstance)->fetch($annotation);
            }
            if ($definedActor !== null) {
                $this->actor = $definedActor;
                return $this;
            }
        }

        $this->actor = $actor;
        return $this;
    }

    public function toString()
    {
        return $this->getFeature() .' (' . $this->getSignature() . ' )';
    }

    public function getSignature()
    {
        return get_class($this->getTestClass()) . "::" . $this->getTestMethod();
    }

    public function getTestClass()
    {
        return $this->testClassInstance;
    }

    public function getTestMethod()
    {
        return $this->testMethod;
    }

    public function getName()
    {
        return $this->testMethod;
    }

    public function getFileName()
    {
        return $this->testFile;
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

    protected function executeHook($I, $hook)
    {
        if (is_callable([$this->testClassInstance, "_$hook"])) {
            $this->invoke("_$hook", [$I, $this->scenario]);
        }
    }


}