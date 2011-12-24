<?php
$I = new CliGuy($scenario);
$I->wantToTest('run command');
$I->runShellCommmand('php codecept run unit');
$I->seeInShellOutput('Codeception');
$I->seeInShellOutput(\Codeception\Codecept::VERSION);
$I->seeInShellOutput('Scenario.run');
$I->seeInShellOutput('OK');

$I->amGoingTo('execute a single Cest file');
$I->runShellCommmand('php codecept run unit Codeception/ScenarioCest.php');
$I->seeInShellOutput('Scenario.run');
$I->dontSeeInShellOutput('FAIL');

