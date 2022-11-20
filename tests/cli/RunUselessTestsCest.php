<?php

class RunUselessTestsCest
{
    public function checkOutput(CliGuy $I): void
    {
        $I->amInPath('tests/data/useless');
        $I->executeCommand('run');
        $I->seeInShellOutput('U UselessCept: Make no assertions');
        $I->seeInShellOutput('U UselessCest: Make no assertions');
        if (DIRECTORY_SEPARATOR === '\\') {
            // Windows shows a plus for a successful test.
            $I->canSeeInShellOutput('+ UselessTest: Expects not to perform assertions');
        } else {
            // Linux/macOS shows a tick for a successful test.
            $I->canSeeInShellOutput('✔ UselessTest: Expects not to perform assertions');
        }
        $I->dontSeeInShellOutput('U UselessTest: Expects not to perform assertions');
        $I->seeInShellOutput('U UselessTest: Make no assertions');
        $I->seeInShellOutput('U UselessTest: Make unexpected assertion');
        $I->seeInShellOutput('OK, but incomplete, skipped, or useless tests!');
        $I->seeInShellOutput('There were 4 useless tests:');

        if (DIRECTORY_SEPARATOR === '/') {
            $I->seeInShellOutput(
                '1) UselessCept: Make no assertions
 Test  tests/unit/UselessCept.php
This test did not perform any assertions'
            );
            $I->seeInShellOutput(
                '
2) UselessCest: Make no assertions
 Test  tests/unit/UselessCest.php:makeNoAssertions
This test did not perform any assertions

Scenario Steps:

 1. // make no assertions'
            );
            $I->seeInShellOutput(
                '
3) UselessTest: Make no assertions
 Test  tests/unit/UselessTest.php:testMakeNoAssertions
This test did not perform any assertions'
            );
            $I->seeInShellOutput(
                '
4) UselessTest: Make unexpected assertion
 Test  tests/unit/UselessTest.php:testMakeUnexpectedAssertion
This test indicates it does not perform assertions but 1 assertions were performed'
            );

            return;
        }

        $I->seeInShellOutput(
            '1) UselessCept: Make no assertions
 Test  tests\unit\UselessCept.php
This test did not perform any assertions'
        );
        $I->seeInShellOutput(
            '
2) UselessCest: Make no assertions
 Test  tests\unit\UselessCest.php:makeNoAssertions
This test did not perform any assertions

Scenario Steps:

 1. // make no assertions'
        );
        $I->seeInShellOutput(
            '
3) UselessTest: Make no assertions
 Test  tests\unit\UselessTest.php:testMakeNoAssertions
This test did not perform any assertions'
        );
        $I->seeInShellOutput(
            '
4) UselessTest: Make unexpected assertion
 Test  tests\unit\UselessTest.php:testMakeUnexpectedAssertion
This test indicates it does not perform assertions but 1 assertions were performed'
        );
    }

    public function checkReports(CliGuy $I): void
    {
        $I->amInPath('tests/data/useless');
        $I->executeCommand('run --report --xml --phpunit-xml --html');
        $I->seeInShellOutput('Useless: 4');
        $I->seeInShellOutput('UselessCept: Make no assertions............................................Useless');
        $I->seeInShellOutput('UselessCest: Make no assertions............................................Useless');
        $I->seeInShellOutput('UselessTest: Make no assertions............................................Useless');
        $I->seeInShellOutput('UselessTest: Expects not to perform assertions.............................Ok');
        $I->seeInShellOutput('UselessTest: Make unexpected assertion.....................................Useless');

        $I->seeInShellOutput('JUNIT XML report generated in');
        $I->seeInShellOutput('PHPUNIT XML report generated in');
        $I->seeInShellOutput('HTML report generated in');
        $I->seeFileFound('report.xml', 'tests/_output');
        $I->seeInThisFile(
            '<testsuite name="unit" tests="5" assertions="1" errors="0" failures="0" skipped="0" useless="4" time="'
        );
        $I->seeInThisFile('<testcase name="Useless"');
        $I->seeInThisFile('<testcase name="makeNoAssertions" class="UselessCest"');
        $I->seeInThisFile('<testcase name="testMakeNoAssertions" class="UselessTest" file="');
        $I->seeInThisFile('<testcase name="testExpectsNotToPerformAssertions" class="UselessTest" file="');
        $I->seeInThisFile('<testcase name="testMakeUnexpectedAssertion" class="UselessTest" file="');
        $I->seeInThisFile('<error>Useless Test</error>');

        $I->seeFileFound('phpunit-report.xml', 'tests/_output');
        $I->seeInThisFile(
            '<testsuite name="unit" tests="5" assertions="1" errors="0" failures="0" skipped="0" useless="4" time="'
        );
        $I->seeInThisFile('<testcase name="Useless"');
        $I->seeInThisFile('<testcase name="makeNoAssertions" class="UselessCest"');
        $I->seeInThisFile('<testcase name="testMakeNoAssertions" class="UselessTest" file="');
        $I->seeInThisFile('<testcase name="testExpectsNotToPerformAssertions" class="UselessTest" file="');
        $I->seeInThisFile('<testcase name="testMakeUnexpectedAssertion" class="UselessTest" file="');
        $I->seeInThisFile('<error>Useless Test</error>');

        $I->seeFileFound('report.html', 'tests/_output');
        $I->seeInThisFile('<td class="scenarioUseless">Useless scenarios:</td>');
        $I->seeInThisFile('<td class="scenarioUselessValue"><strong>4</strong></td>');
        $I->seeInThisFile('UselessCest');
        $I->seeInThisFile('UselessTest');
        $I->seeInThisFile('UselessCept');
    }
}
