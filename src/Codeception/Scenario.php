<?php
namespace Codeception;

use Codeception\Exception\TestRuntimeException;

class Scenario
{
    /**
     * @var    \Codeception\TestCase
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
    protected $running = false;
    protected $blocker = null;
    protected $groups = [];
    protected $env = [];

    protected $currents = [];

    /**
     * Constructor.
     *
     * @param  \Codeception\TestCase $test
     */
    public function __construct(\Codeception\TestCase $test, $currents = [])
    {
        $this->test = $test;
        $this->currents = $currents;
    }

    public function group($group)
    {
        if (!is_array($group)) {
            $this->groups[] = $group;
            return;
        }
        foreach ($group as $t) {
            $this->group($t);
        }
    }

    public function env($env)
    {
        if (!is_array($env)) {
            $this->env[] = $env;
            return;
        }
        foreach ($env as $e) {
            $this->env($e);
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

    public function getEnv()
    {
        return $this->env;
    }

    public function setFeature($feature)
    {
        $this->feature = $feature;
    }

    public function skip($reason = "")
    {
        $this->blocker = new \Codeception\Step\Skip($reason, []);
    }

    public function incomplete($reason = "")
    {
        $this->blocker = new \Codeception\Step\Incomplete($reason, []);
    }

    protected function ignore()
    {
        $this->blocker = new \Codeception\Step\Ignore;
    }

    public function runStep(Step $step)
    {
        $this->steps[] = $step;
        $result = $this->test->runStep($step);
        $step->executed = true;
        return $result;
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
                $text .= 'I ' . $step->getHtmlAction() . '<br/>';
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
        $text = str_replace(['"\'', '\'"'], ["'", "'"], $text);
        $text = strtoupper('I want to ' . $this->getFeature()) . "\r\n\r\n" . $text;
        return $text;

    }

    public function comment($comment)
    {
        $this->runStep(new \Codeception\Step\Comment($comment, []));
    }

    public function stopIfBlocked()
    {
        if ($this->isBlocked()) {
            return $this->blocker->run();
        }
    }

    public function current($key)
    {
        if (!isset($this->currents[$key])) {
            throw new TestRuntimeException("Current $key is not set in this scenario");
        }
        return $this->currents[$key];
    }

    public function isBlocked()
    {
        return (bool)$this->blocker;
    }
}
