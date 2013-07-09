<?php
$I = new CliGuy($scenario);
$I->wantTo('use alternative formatter delivered through extensions');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run tests/skipped/CommentsCept.php -c codeception_extended.yml');
$I->dontSeeInShellOutput("Trying to talk, just talk");
$I->dontSeeInShellOutput('As a very lazy qa');
$I->seeInShellOutput('[+] talk, just talk');

