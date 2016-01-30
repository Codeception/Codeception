<?php
namespace Codeception\Lib\Actor\Shared;

trait Comment
{
    /**
     * @return \Codeception\Scenario
     */
    abstract protected function getScenario();

    public function expectTo($prediction)
    {
        return $this->comment('I expect to ' . $prediction);
    }

    public function expect($prediction)
    {
        return $this->comment('I expect ' . $prediction);
    }

    public function amGoingTo($argumentation)
    {
        return $this->comment('I am going to ' . $argumentation);
    }

    public function am($role)
    {
        $role = trim($role);

        if (stripos('aeiou', $role[0]) !== false) {
            return $this->comment('As an ' . $role);
        }

        return $this->comment('As a ' . $role);
    }

    public function lookForwardTo($achieveValue)
    {
        return $this->comment('So that I ' . $achieveValue);
    }

    public function comment($description)
    {
        $this->getScenario()->comment($description);
        return $this;
    }
}
