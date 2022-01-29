<?php

$I = new CliGuy($scenario);
$I->wantTo('see that tests have no assertions');
$I->amInPath('tests/data/useless');
$I->executeCommand('run');
$I->seeInShellOutput('U UselessCept: Make no assertions');
$I->seeInShellOutput('U UselessCest: Make no assertions');
$I->seeInShellOutput('U UselessTest: Make no assertions');
$I->seeInShellOutput('U UselessTest: Make unexpected assertion');
$I->seeInShellOutput('OK, but incomplete, skipped, or useless tests!');
$I->seeInShellOutput('There were 4 useless tests:');
$I->seeInShellOutput('1) UselessCept: Make no assertions
 Test  tests/unit/UselessCept.php
This test did not perform any assertions'
);
$I->seeInShellOutput('
2) UselessCest: Make no assertions
 Test  tests/unit/UselessCest.php:makeNoAssertions
This test did not perform any assertions

Scenario Steps:

 1. // make no assertions'
);
$I->seeInShellOutput('
3) UselessTest: Make no assertions
 Test  tests/unit/UselessTest.php:testMakeNoAssertions
This test did not perform any assertions'
);
$I->seeInShellOutput('
4) UselessTest: Make unexpected assertion
 Test  tests/unit/UselessTest.php:testMakeUnexpectedAssertion
This test is annotated with "@doesNotPerformAssertions" but performed 1 assertions'
);
