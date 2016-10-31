<?php
namespace Codeception\Test;

use Codeception\Example;
use Codeception\Lib\Console\Message;
use Codeception\Lib\Parser;
use Codeception\Step\Comment;
use Codeception\Util\Annotation;
use Codeception\Util\ReflectionHelper;

/**
 * Executes tests delivered in Cest format.
 *
 * Handles loading of Cest cases, executing specific methods, following the order from `@before` and `@after` annotations.
 */
class Cest extends Test implements
    Interfaces\ScenarioDriven,
    Interfaces\Reported,
    Interfaces\Dependent,
    Interfaces\StrictCoverage
{
    use Feature\ScenarioLoader;
    /**
     * @var Parser
     */
    protected $parser;
    protected $testClassInstance;
    protected $testMethod;

    public function __construct($testClass, $methodName, $fileName)
    {
        $metadata = new Metadata();
        $metadata->setName($methodName);
        $metadata->setFilename($fileName);
        $this->setMetadata($metadata);
        $this->testClassInstance = $testClass;
        $this->testMethod = $methodName;
        $this->createScenario();
        $this->parser = new Parser($this->getScenario(), $this->getMetadata());
    }

    public function preload()
    {
        $this->scenario->setFeature($this->getSpecFromMethod());
        $code = $this->getSourceCode();
        $this->parser->parseFeature($code);
        $this->parser->attachMetadata(Annotation::forMethod($this->testClassInstance, $this->testMethod)->raw());
        $this->getMetadata()->getService('di')->injectDependencies($this->testClassInstance);

        // add example params to feature
        if ($this->getMetadata()->getCurrent('example')) {
            $step = new Comment('', $this->getMetadata()->getCurrent('example'));
            $this->getScenario()->setFeature($this->getScenario()->getFeature() . ' | '. $step->getArgumentsAsString(100));
        }
    }

    public function getSourceCode()
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

    public function test()
    {
        $actorClass = $this->getMetadata()->getCurrent('actor');
        $I = new $actorClass($this->getScenario());
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

    protected function executeHook($I, $hook)
    {
        if (is_callable([$this->testClassInstance, "_$hook"])) {
            $this->invoke("_$hook", [$I, $this->scenario]);
        }
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
            $this->getMetadata()->getService('di')->set($class);
        }
        $this->getMetadata()->getService('di')->injectDependencies($this->testClassInstance, $methodName, $context);
    }
    protected function executeTestMethod($I)
    {
        if (!method_exists($this->testClassInstance, $this->testMethod)) {
            throw new \Exception("Method {$this->testMethod} can't be found in tested class");
        }

        if ($this->getMetadata()->getCurrent('example')) {
            $this->invoke($this->testMethod, [$I, $this->scenario, new Example($this->getMetadata()->getCurrent('example'))]);
            return;
        }
        $this->invoke($this->testMethod, [$I, $this->scenario]);
    }

    public function toString()
    {
        return sprintf('%s: %s', ReflectionHelper::getClassShortName($this->getTestClass()), Message::ucfirst($this->getFeature()));
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

    public function getDependencies()
    {
        $names = [];
        foreach ($this->getMetadata()->getDependencies() as $required) {
            if ((strpos($required, ':') === false) and method_exists($this->getTestClass(), $required)) {
                $required = get_class($this->getTestClass()) . ":$required";
            }
            $names[] = $required;
        }
        return $names;
    }

    public function getLinesToBeCovered()
    {
        $class  = get_class($this->getTestClass());
        $method = $this->getTestMethod();

        return \PHPUnit_Util_Test::getLinesToBeCovered($class, $method);
    }

    public function getLinesToBeUsed()
    {
        $class  = get_class($this->getTestClass());
        $method = $this->getTestMethod();

        return \PHPUnit_Util_Test::getLinesToBeUsed($class, $method);
    }
}
