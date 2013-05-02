<?php
$I = new CliGuy($scenario);
$I->wantTo('perform actions and see result');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run skipped SkipMeCept.php');
$I->seeInShellOutput('(SkipMeCept.php)  SKIPPED');
$I->seeInShellOutput('OK, but incomplete or skipped tests!');

