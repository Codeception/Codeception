<?php 
$I = new ScenarioGuy($scenario);
$I->wantTo('run scenario substeps');
$I->amInPath('.');
$I->seeCodeCoverageFilesArePresent();
