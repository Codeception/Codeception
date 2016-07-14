<?php
$I = new CliGuy($scenario);
$I->wantTo('run skipped test');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run skipped SkipMeCept.php');
$I->seeInShellOutput("S SkipMeCept: Skip it");
$I->seeInShellOutput('OK, but incomplete, skipped, or risky tests!');
$I->seeInShellOutput('run with `-v` to get more info');
