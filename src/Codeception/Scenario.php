<?php
namespace Codeception;

use Codeception\Event\StepEvent;
use Codeception\Exception\ConditionalAssertionFailed;
use Codeception\Lib\Notification;
use Codeception\Step;
use Codeception\Test\Interfaces\Configurable;
use Codeception\Test\Interfaces\ScenarioDriven;
use Codeception\Test\Test;

class Scenario
{
    /**
     * @var    \Codeception\Test\Test
     */
    protected $test;

    /**
     * @var    array
     */
    protected $steps = [];

    /**
     * @var    string
     */
    protected $feature;

    /**
     * Constructor
     *
     * @param Test $test
     */
    public function __construct(Test $test)
    {
        $this->test = $test;
    }

    public function setFeature($feature)
    {
        $this->feature = $feature;
    }

    public function current($key)
    {
        return $this->test->getMetadata()->getCurrent($key);
    }

    public function runStep(Step $step)
    {
        $this->steps[] = $step;
        $result = null;
        $this->test->getDispatcher()->dispatch(Events::STEP_BEFORE, new StepEvent($this->test, $step));
        try {
            $result = $step->run($this->test->getModuleContainer());
        } catch (ConditionalAssertionFailed $f) {
            $this->test->getTestResultObject()->addFailure(clone($this), $f, $this->test->getTestResultObject()->time());
        } catch (\Exception $e) {
            $this->test->getDispatcher()->dispatch(Events::STEP_AFTER, new StepEvent($this->test, $step));
            throw $e;
        }
        $this->test->getDispatcher()->dispatch(Events::STEP_AFTER, new StepEvent($this->test, $step));
        $step->executed = true;
        return $result;
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

    public function getFeature()
    {
        return $this->feature;
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
        $text = "<h3>" . strtoupper('I want to ' . $this->getFeature()) . "</h3>" . $text;
        return $text;

    }

    public function getText()
    {
        $text = implode("\r\n", $this->getSteps());
        $text = str_replace(array('"\'','\'"'), array("'","'"), $text);
        $text = strtoupper('I want to ' . $this->getFeature()) . str_repeat("\r\n", 2) . $text . str_repeat("\r\n", 2);
        return $text;

    }

    public function __call($method, $args)
    {
        // all methods were deprecated and removed from here
        Notification::deprecate("\$scenario->$method() was deprecated in 2.1 and removed. Don't use it");
    }
}
