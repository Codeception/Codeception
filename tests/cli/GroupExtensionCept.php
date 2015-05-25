<?php
$I = new CliGuy($scenario);
$I->wantTo('see that my group extension works');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run skipped -g notorun -c codeception_grouped.yml');
$I->seeInShellOutput("======> Entering NoGroup Test Scope\nMake it incomplete");
$I->seeInShellOutput('<====== Ending NoGroup Test Scope');

