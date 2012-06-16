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

    protected $finislizers = array();

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

    public function condition($arguments)
    {
        return $this->addStep(new \Codeception\Step\Condition($arguments));
    }

    public function action($arguments)
    {
        return $this->addStep(new \Codeception\Step\Action($arguments));
    }

    public function assertion($arguments)
    {
        return $this->addStep(new \Codeception\Step\Assertion($arguments));
    }

    public function run()
    {
        foreach ($this->steps as $k => $step)
        {
            $this->currentStep = $k;
            $this->test->runStep($step);
        }

        foreach ($this->finislizers as $fin) {
            $fin();
        }

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
        return $this->steps;
    }

	public function getFeature() {
	    return $this->feature;
	}

	public function comment($comment) {
		$this->addStep(new \Codeception\Step\Comment($comment));
	}

    public function getCurrentStep()
    {
        return $this->currentStep;
    }

    public function prepare(\Closure $lambda) {
        $res = $lambda();
        return $res;
    }

    public function finilize(\Closure $lambda) {
        $this->finislizers[] = $lambda;
    }

}
