<?php
$I = new CliGuy($scenario);
$I->wantTo('see that my group extension works');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run skipped -g notorun -c codeception_grouped.yml');
$I->dontSeeInShellOutput("======> Entering NoGroup Test Scope\nMake it incomplete");
$I->dontSeeInShellOutput('<====== Ending NoGroup Test Scope');
$I->executeCommand('run dummy -g ok -c codeception_grouped.yml');
$I->dontSeeInShellOutput("======> Entering Ok Test Scope\nMake it incomplete");
$I->dontSeeInShellOutput('<====== Ending Ok Test Scope');

