<?php
$I = new CliGuy($scenario);
$I->wantTo('execute incomplete test');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run skipped IncompleteMeCept.php');
$I->seeShellOutputMatches("~\(IncompleteMeCept\)[\s\.]*?Incomplete~");
$I->seeInShellOutput('OK, but incomplete, skipped, or risky tests!');