<?php
$I = new CliGuy($scenario);
$I->wantTo('see that exception in before does not cause fatal error in after');
$I->amInPath('tests/data/exception_in_before');
$I->executeFailCommand('run --xml --no-ansi');
$I->seeInShellOutput('[Exception] in before');
$I->dontSeeInShellOutput('[RuntimeException] in cept');
$I->dontSeeInShellOutput('[RuntimeException] in cest');
$I->dontSeeInShellOutput('[RuntimeException] in gherkin');
$I->seeInShellOutput('Tests: 4, Assertions: 0, Errors: 5');

//@todo if Unit format is ever fixed in PHPUnit, uncomment these lines
//$I->dontSeeInShellOutput('[RuntimeException] in test');
//$I->seeInShellOutput('Tests: 4, Assertions: 0, Errors: 4');
