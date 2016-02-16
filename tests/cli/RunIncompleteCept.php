<?php
$I = new CliGuy($scenario);
$I->wantTo('execute incomplete test');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run skipped IncompleteMeCept.php');
$I->seeInShellOutput("I Make it incomplete");
$I->seeInShellOutput('OK, but incomplete, skipped, or risky tests!');