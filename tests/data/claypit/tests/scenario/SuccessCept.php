<?php 
$I = new ScenarioGuy($scenario);
$I->wantTo('check that suite config is available');
$I->amInPath('.');
$I->seeFileFound('scenario.suite.yml');