<?php
namespace Codeception;

use Codeception\Event\StepEvent;
use Codeception\Exception\ConditionalAssertionFailed;
use Codeception\Lib\Notification;
use Codeception\Step;
use Codeception\Test\Metadata;

class Scenario
{
    /**
     * @var TestInterface
     */
    protected $test;
    /**
     * @var Metadata
     */
    protected $metadata;

    /**
     * @var    array
     */
    protected $steps = [];

    /**
     * @var    string
     */
    protected $feature;

    protected $metaStep;

    /**
     * Constructor
     *
     * @param TestInterface $test
     */
    public function __construct(TestInterface $test)
    {
        $this->metadata = $test->getMetadata();
        $this->test = $test;
    }

    public function setFeature($feature)
    {
        $this->metadata->setFeature($feature);
    }

    public function getFeature()
    {
        return $this->metadata->getFeature();
    }

    public function current($key)
    {
        return $this->metadata->getCurrent($key);
    }

    public function runStep(Step $step)
    {
        $step->saveTrace();
        if ($this->metaStep instanceof Step\Meta) {
            $step->setMetaStep($this->metaStep);
        }
        $this->steps[] = $step;
        $result = null;
        $this->metadata->getService('dispatcher')->dispatch(Events::STEP_BEFORE, new StepEvent($this->test, $step));
        try {
            $result = $step->run($this->metadata->getService('modules'));
        } catch (ConditionalAssertionFailed $f) {
            $result = $this->test->getTestResultObject();
            if (is_null($result)) {
                $this->metadata->getService('dispatcher')->dispatch(Events::STEP_AFTER, new StepEvent($this->test, $step));
                throw $f;
            } else {
                $result->addFailure(clone($this->test), $f, $result->time());
            }
        } catch (\Exception $e) {
            $this->metadata->getService('dispatcher')->dispatch(Events::STEP_AFTER, new StepEvent($this->test, $step));
            throw $e;
        }
        $this->metadata->getService('dispatcher')->dispatch(Events::STEP_AFTER, new StepEvent($this->test, $step));
        $step->executed = true;
        return $result;
    }

    public function addStep(Step $step)
    {
        $this->steps[] = $step;
    }

    /**
     * Returns the steps of this scenario.
     *
     * @return array
     */
    public function getSteps()
    {
        return $this->steps;
    }

    public function getHtml()
    {
        $text = '';
        foreach ($this->getSteps() as $step) {
            /** @var Step $step */
            if ($step->getName() !== 'Comment') {
                $text .= $step->getHtml() . '<br/>';
            } else {
                $text .= trim($step->getHumanizedArguments(), '"') . '<br/>';
            }
        }
        $text = str_replace(['"\'', '\'"'], ["'", "'"], $text);
        $text = "<h3>" . mb_strtoupper('I want to ' . $this->getFeature(), 'utf-8') . "</h3>" . $text;
        return $text;
    }

    public function getText()
    {
        $text = '';
        foreach ($this->getSteps() as $step) {
            $text .= $step->getPrefix() . "$step \r\n";
        }
        $text = trim(str_replace(['"\'', '\'"'], ["'", "'"], $text));
        $text = mb_strtoupper('I want to ' . $this->getFeature(), 'utf-8') . "\r\n\r\n" . $text . "\r\n\r\n";
        return $text;
    }

    public function comment($comment)
    {
        $this->runStep(new \Codeception\Step\Comment($comment, []));
    }

    public function skip($message = '')
    {
        throw new \PHPUnit_Framework_SkippedTestError($message);
    }

    public function incomplete($message = '')
    {
        throw new \PHPUnit_Framework_IncompleteTestError($message);
    }

    public function __call($method, $args)
    {
        // all methods were deprecated and removed from here
        trigger_error("Codeception: \$scenario->$method() has been deprecated and removed. Use annotations to pass scenario params", E_USER_DEPRECATED);
    }

    /**
     * @param null $metaStep
     */
    public function setMetaStep($metaStep)
    {
        $this->metaStep = $metaStep;
    }
}
