<?php
$I = new CodeGuy($scenario);
$I->testMethod('\Codeception\Scenario.run');
$I->haveFakeClass($test = Stub::makeEmpty('\Codeception\TestCase', array('runStep' => function() { })));
$I->haveFakeClass($scenario = Stub::make('\Codeception\Scenario', array(
    'test' => $test,
    'steps' => array(Stub::makeEmpty('\Codeception\Step\Action'), Stub::makeEmpty('\Codeception\Step\Comment')))));
$I->executeTestedMethodOn($scenario);
$I->seeMethodInvoked($test,'runStep');
$I->seePropertyEquals($scenario, 'currentStep', 1);