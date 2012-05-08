<?php
$I = new CliGuy($scenario);
$I->wantTo('generate scenarios сценарий');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('generate:scenarios dummy');
$I->seeFileFound('File_Exists.txt','tests/_data/scenarios');
$I->seeInThisFile("I WANT TO CHECK CONFIG EXISTS");
$I->seeInThisFile('I see file found "codeception.yml"');

