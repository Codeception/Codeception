<?php

class PartialFailedCest
{
    public function testCaseOne(ScenarioGuy $I)
    {
        $I->amInPath('.');
        $I->seeFileFound('scenario.suite.yml');
    }

    public function testCaseTwo(ScenarioGuy $I)
    {
        $I->amInPath('.');
        $I->seeFileFound('testcasetwo.txt');
    }

    public function testCaseThree(ScenarioGuy $I)
    {
        $I->amInPath('.');
        $I->seeFileFound('testcasethree.txt');
    }

}
