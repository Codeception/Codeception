<?php 
$I = new ScenarioGuy($scenario);
$I->wantTo('fail when file is not found');
$I->amInPath('.');
$I->seeFileFound('games.zip');