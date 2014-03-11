<?php
$I = new CliGuy($scenario);
$I->wantTo('run skipped test');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run skipped SkipMeCept.php');
$I->seeShellOutputMatches("~\(SkipMeCept.php\)\s*?Skipped~");
$I->seeInShellOutput('OK, but incomplete, skipped, or risky tests!');

