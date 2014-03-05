<?php
namespace Codeception\TestCase\Shared;

use Codeception\Event\StepEvent;
use Codeception\Events;
use Codeception\Exception\ConditionalAssertionFailed;
use Codeception\Step;

trait ScenarioRunner
{
    use Configuration;

    protected $trace = [];

    public function runStep(Step $step)
    {
        $this->trace[] = $step;
        $this->fire(Events::STEP_BEFORE, new StepEvent($this, $step));
        try {
            $result = $step->run();
        } catch (ConditionalAssertionFailed $f) {
            $result = $this->getTestResultObject();
            $result->addFailure(clone($this), $f, $result->time());
        } catch (\Exception $e) {
            $this->fire(Events::STEP_AFTER, new StepEvent($this, $step));
            throw $e;
        }
        $this->fire(Events::STEP_AFTER, new StepEvent($this, $step));
        return $result;
    }

    public function getFeature()
    {
        return $this->scenario->getFeature();
    }

    public function getTrace()
    {
        return $this->trace;
    }

    public function getScenarioText($format = 'text')
    {
        $code = $this->getRawBody();
        $this->parser->parseFeature($code);
        $this->parser->parseSteps($code);
        if ($format == 'html') {
            return $this->scenario->getHtml();
        }
        return $this->scenario->getText();
    }

    abstract function getRawBody();


}