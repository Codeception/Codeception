<?php
namespace Codeception;
 
class Scenario {
    /**
     * @var    \Codeception\TestCase
     */
    protected $test;

    /**
     * @var    array
     */
    protected $steps = array();

    /**
     * @var    string
     */
	protected $feature;

    protected $currentStep = 0;

    protected $finalizers = array();

    protected $running = false;

    protected $preloadedSteps = array();

    /**
     * Constructor.
     *
     * @param  \Codeception\TestCase $test
     */
    public function __construct(\Codeception\TestCase $test)
    {
		$this->test = $test;
    }


	public function setFeature($feature) {
	    $this->feature = $feature;
	}

    public function condition($action, $arguments)
    {
        return $this->addStep(new \Codeception\Step\Condition($action, $arguments));
    }

    public function action($action, $arguments)
    {
        return $this->addStep(new \Codeception\Step\Action($action, $arguments));
    }

    public function assertion($action, $arguments)
    {
        return $this->addStep(new \Codeception\Step\Assertion($action, $arguments));
    }

    public function runStep()
    {
        if (empty($this->steps)) return;

        $step = $this->lastStep();
        if (!$step->executed) {
            $result = $this->test->runStep($step);
            $this->currentStep++;
            $step->executed = true;
            return $result;
        }
    }

    /**
     * @return \Codeception\Step
     */
    protected function lastStep()
    {
        return end($this->steps);
    }

    protected function addStep(\Codeception\Step $step)
    {
        $this->steps[] = $step;
        return $this->test;
    }

    /**
     * Returns the steps of this scenario.
     *
     * @return array
     */
    public function getSteps()
    {
        if (!$this->running) return $this->steps;
        return $this->preloadedSteps;
    }

	public function getFeature() {
	    return $this->feature;
	}

	public function comment($comment) {
		$this->addStep(new \Codeception\Step\Comment($comment,array()));
	}

    public function getCurrentStep()
    {
        return $this->currentStep;
    }
    
    public function run() {
        if ($this->running()) return;
        $this->running = true;
        $this->preloadedSteps = $this->steps;
        $this->steps = array();
    }

    public function running()
    {
        return $this->running;
    }

    public function preload() {
        return !$this->running;
    }

    public function skip($reason = "")
    {
        if ($this->running) throw new \PHPUnit_Framework_SkippedTestError($reason);
    }

    public function incomplete($reason = "")
    {
        if ($this->running) throw new \PHPUnit_Framework_IncompleteTestError($reason);
    }

}
