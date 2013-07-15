<?php
$I = new CliGuy($scenario);
$I->wantTo('execute incomplete test');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run skipped IncompleteMeCept.php');
$I->seeInShellOutput('(IncompleteMeCept.php) - Incomplete');
$I->seeInShellOutput('OK, but incomplete or skipped tests!');