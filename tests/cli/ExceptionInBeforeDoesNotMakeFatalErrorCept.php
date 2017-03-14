<?php
$I = new CliGuy($scenario);
$I->wantTo('see that exception in before does not cause fatal error in after');
$I->amInPath('tests/data/exception_in_before');
$I->executeFailCommand('run --xml --no-ansi');
$I->seeInShellOutput('Tests: 1, Assertions: 0, Errors: 1');