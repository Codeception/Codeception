<?php
namespace Codeception\Test\Format;

use Codeception\Lib\Parser;
use Codeception\Test\Feature\ScenarioLoader;
use Codeception\Test\Feature\ScenarioRunner;
use Codeception\Test\Interfaces\Reported;
use Codeception\Test\Interfaces\ScenarioDriven;
use Codeception\Util\Annotation;

class Cest extends \Codeception\Test\Test implements ScenarioDriven, Reported
{

    use ScenarioRunner;
    use ScenarioLoader;

    /**
     * @var Parser
     */
    protected $parser;
    protected $testClassInstance;
    protected $testMethod;

    public function __construct($testClass, $methodName, $fileName)
    {
        $this->getMetadata()->setName($methodName);
        $this->getMetadata()->setFilename($fileName);
        $this->testClassInstance = $testClass;
        $this->testMethod = $methodName;
        $this->createScenario();
        $this->parser = new Parser($this->getScenario(), $this->getMetadata());
    }

    public function test()
    {
        try {
            $this->invoke('_before');
            $this->executeBeforeMethods($this->testMethod);
            $this->executeTestMethod();
            $this->executeAfterMethods($this->testMethod);
            $this->invoke('_after');
        } catch (\Exception $e) {
            $this->invoke('_failed');
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
        $this->getDi()->injectDependencies($this->testClassInstance);
    }

    public function getRawBody()
    {
        $method = new \ReflectionMethod($this->testClassInstance, $this->testMethod);
        $start_line = $method->getStartLine() - 1; // it's actually - 1, otherwise you wont get the function() block
        $end_line = $method->getEndLine();
        $source = file($method->getFileName());
        return implode("", array_slice($source, $start_line, $end_line - $start_line));
    }

    public function getSpecFromMethod()
    {
        $text = $this->testMethod;
        $text = preg_replace('/([A-Z]+)([A-Z][a-z])/', '\\1 \\2', $text);
        $text = preg_replace('/([a-z\d])([A-Z])/', '\\1 \\2', $text);
        $text = strtolower($text);
        return $text;
    }


    protected function executeBeforeMethods($testMethod)
    {
        $annotations = \PHPUnit_Util_Test::parseTestMethodAnnotations(get_class($this->testClassInstance), $testMethod);
        if (!empty($annotations['method']['before'])) {
            foreach ($annotations['method']['before'] as $m) {
                $this->executeContextMethod(trim($m));
            }
        }
    }

    protected function executeAfterMethods($testMethod)
    {
        $annotations = \PHPUnit_Util_Test::parseTestMethodAnnotations(get_class($this->testClassInstance), $testMethod);
        if (!empty($annotations['method']['after'])) {
            foreach ($annotations['method']['after'] as $m) {
                $this->executeContextMethod(trim($m));
            }
        }
    }

    protected function executeContextMethod($context)
    {
        if (method_exists($this->testClassInstance, $context)) {
            $this->executeBeforeMethods($context);
            $this->invoke($context);
            $this->executeAfterMethods($context);
            return;
        }

        throw new \LogicException(
            "Method $context defined in annotation but does not exist in " . get_class($this->testClassInstance)
        );
    }

    protected function invoke($methodName)
    {
        if (!is_callable([$this->testClassInstance, $methodName])) {
            return;
        }
        $this->getDi()->set($this->getScenario());
        $defaults = [];

        // creating dependencies for $I and $scenario without classes set in params
        if ($actor = $this->getMetadata()->get('actor')) {
            $defaults[] = $this->getDi()->get($actor);
            $defaults[] = $this->getScenario();
        }
        $this->getDi()->injectDependencies($this->testClassInstance, $methodName, $defaults);
    }

    protected function executeTestMethod()
    {
        $testMethodSignature = [$this->testClassInstance, $this->testMethod];
        if (! is_callable($testMethodSignature)) {
            throw new \Exception("Method {$this->testMethod} can't be found in tested class");
        }
        $this->invoke($this->testMethod);
    }

    public function toString()
    {
        return $this->getFeature() .' (' . $this->getSignature() . ' )';
    }

    public function getSignature()
    {
        return get_class($this->getTestClass()) . ":" . $this->getTestMethod();
    }

    public function getTestClass()
    {
        return $this->testClassInstance;
    }

    public function getTestMethod()
    {
        return $this->testMethod;
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

    protected function getParser()
    {
        return $this->parser;
    }


}