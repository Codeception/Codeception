<?php
class TestCaseCest
{
    public $class = '\Codeception\TestCase';

    public function runStep(\CodeGuy $I) {
        $I->wantTo('run action from module defined in current step');

        $I->haveFakeModule($module = Stub::make('\Codeception\Module', array('runAction' => function() {} )));
        $I->haveFakeClass($step = \Stub::make('\Codeception\Step\Action', array('action' => 'runAction', 'arguments' => array())));
        $I->haveFakeClass($test = \Stub::makeEmpty('\Codeception\TestCase', array('output' => new \Codeception\Output(false))));

        $I->executeTestedMethodOn($test, $step);

        $I->seeMethodInvoked($module, '_beforeStep');
        $I->seeMethodInvoked($module, '_afterStep');
        $I->seeMethodInvoked($module, 'runAction');
    }



}
