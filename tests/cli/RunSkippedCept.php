<?php
$I = new CliGuy($scenario);
$I->wantTo('perform actions and see result');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run skipped SkipMeCept.php');
$I->seeInShellOutput('(SkipMeCept.php) - Skipped');
$I->seeInShellOutput('OK, but incomplete or skipped tests!');

