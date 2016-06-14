<?php
$I = new CliGuy($scenario);
$I->wantTo('see that my group events fire only once');
$I->amInPath('tests/data/claypit');
$I->executeCommand('run dummy -g countevents -c codeception_grouped.yml');
$I->seeInShellOutput('Group Before Events: 1');
$I->dontSeeInShellOutput('Group Before Events: 2');
$I->seeInShellOutput('Group After Events: 1');
$I->dontSeeInShellOutput('Group After Events: 2');