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

    protected $running = false;

    protected $preloadedSteps = array();

    protected $blocker = null;

    protected $groups = array();

    /**
     * Constructor.
     *
     * @param  \Codeception\TestCase $test
     */
    public function __construct(\Codeception\TestCase $test)
    {
		$this->test = $test;
    }

    public function group($group)
    {
        if (is_array($group)) {
            foreach ($group as $t) {
                $this->group($t);
            }
        } else {
            $this->groups[] = $group;
        }
    }

    public function groups()
    {
        $this->group(func_get_args());
    }

    public function getGroups()
    {
        return $this->groups;
    }


	public function setFeature($feature) {
	    $this->feature = $feature;
	}

    public function skip($reason = "")
    {
        $this->blocker = new \Codeception\Step\Skip($reason, array());
    }

    public function incomplete($reason = "")
    {
        $this->blocker = new \Codeception\Step\Incomplete($reason, array());
    }

    protected function ignore()
    {
        $this->blocker = new \Codeception\Step\Ignore;
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

    public function addStep(\Codeception\Step $step)
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

    public function getHtml()
    {
        $text = '';
        foreach($this->getSteps() as $step) {
            /** @var Step $step */
            if ($step->getName() !== 'Comment') {
                $text .= 'I ' . $step->getHtmlAction() . '<br/>';
            } else {
                $text .= trim($step->getHumanizedArguments(), '"') . '<br/>';
            }
        }
        $text = str_replace(array('((', '))'), array('...', ''), $text);
        $text = "<h3>" . strtoupper('I want to ' . $this->getFeature()) . "</h3>" . $text;
        return $text;

    }

    public function getText()
    {
        $text = implode("\r\n", $this->getSteps());
        $text = str_replace(array('((', '))'), array('...', ''), $text);
        $text = strtoupper('I want to ' . $this->getFeature()) . "\r\n\r\n" . $text;
        return $text;

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
        if ($this->blocker) return $this->blocker->run();

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

}
