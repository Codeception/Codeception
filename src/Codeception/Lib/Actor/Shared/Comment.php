<?php
namespace Codeception\Lib\Actor\Shared;

use Symfony\Component\Console\Tests\Input\ArrayInputTest;

trait Comment
{
    public function expectTo($prediction)
    {
        return $this->comment('I expect to ' . $prediction, "expect");
    }

    public function expect($prediction)
    {
        return $this->comment('I expect ' . $prediction, "expect");
    }

    public function amGoingTo($argumentation)
    {
        return $this->comment('I am going to ' . $argumentation, "goingTo");
    }

    public function am($role) {
        return $this->comment('As a ' . $role, "Iam");
    }

    public function lookForwardTo($achieveValue)
    {
        return $this->comment('So that I ' . $achieveValue);
    }

    public function comment($description, $commentType = null)
    {
        $this->scenario->comment($description, $commentType);
        return $this;
    }

    public function testCase($testCase)
    {
        $this->scenario->comment($testCase, "testCase");
    }

    public function wantToTestFunction($testFunction)
    {
        return $this->comment('Test  ' . $testFunction . ' function');
    }

    public function useDataForTest(array $testData)
    {
        return $this->comment("use data:\n" . (json_encode($testData)), "data");
    }

    public function doStep($step){
        return $this->comment("-" . (json_encode($step)), "step");
    }
}