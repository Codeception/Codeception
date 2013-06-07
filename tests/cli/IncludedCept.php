<?php
$I = new CliGuy($scenario);
$I->wantTo('run test suite from included config');
$I->amInPath('tests/data');
$I->executeCommand('run');
$I->seeInShellOutput('Running ');

