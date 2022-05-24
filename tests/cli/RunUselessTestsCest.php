<?php

class RunUselessTestsCest
{
    public function checkOutput(CliGuy $I)
    {
        $I->amInPath('tests/data/useless');
        $I->executeCommand('run -v');
        // $I->seeInShellOutput('U UselessCept: Make no assertions');
        // $I->seeInShellOutput('U UselessCest: Make no assertions');
        $I->seeInShellOutput('U UselessTest: Make no assertions');
        $I->seeInShellOutput('OK, but incomplete, skipped, or risky tests!');

        if (version_compare(\PHPUnit\Runner\Version::id(), '7.2.0', '>=')) {
            $I->seeInShellOutput('There were 2 risky tests:');
            $I->seeInShellOutput('U UselessTest: Make unexpected assertion');
        } else {
            $I->seeInShellOutput('There was 1 risky test:');
            $I->seeInShellOutput('S UselessTest: Make unexpected assertion');
        }

        if (DIRECTORY_SEPARATOR === '/') {
//            $I->seeInShellOutput(
//                '1) UselessCept: Make no assertions
// Test  tests/unit/UselessCept.php
//This test did not perform any assertions'
//            );
//            $I->seeInShellOutput(
//                '
//2) UselessCest: Make no assertions
// Test  tests/unit/UselessCest.php:makeNoAssertions
//This test did not perform any assertions
//
//Scenario Steps:
//
// 1. // make no assertions'
//            );
            $I->seeInShellOutput(
                '
1) UselessTest: Make no assertions
 Test  tests/unit/UselessTest.php:testMakeNoAssertions
This test did not perform any assertions'
            );
            if (version_compare(\PHPUnit\Runner\Version::id(), '7.2.0', '>=')) {
                $I->seeInShellOutput(
                    '
2) UselessTest: Make unexpected assertion
 Test  tests/unit/UselessTest.php:testMakeUnexpectedAssertion
This test is annotated with "@doesNotPerformAssertions" but performed 1 assertions'
                );
            }

            return;
        }

//        $I->seeInShellOutput(
//            '1) UselessCept: Make no assertions
// Test  tests\unit\UselessCept.php
//This test did not perform any assertions'
//        );
//        $I->seeInShellOutput(
//            '
//2) UselessCest: Make no assertions
// Test  tests\unit\UselessCest.php:makeNoAssertions
//This test did not perform any assertions
//
//Scenario Steps:
//
// 1. // make no assertions'
//        );
        $I->seeInShellOutput(
            '
1) UselessTest: Make no assertions
 Test  tests\unit\UselessTest.php:testMakeNoAssertions
This test did not perform any assertions'
        );
        if (version_compare(\PHPUnit\Runner\Version::id(), '7.2.0', '>=')) {
            $I->seeInShellOutput(
                '
2) UselessTest: Make unexpected assertion
 Test  tests\unit\UselessTest.php:testMakeUnexpectedAssertion
This test is annotated with "@doesNotPerformAssertions" but performed 1 assertions'
            );
        }
    }

    public function checkReports(CliGuy $I)
    {
        $I->amInPath('tests/data/useless');
        $I->executeCommand('run --report --xml --phpunit-xml --html');

        if (version_compare(\PHPUnit\Runner\Version::id(), '7.2.0', '>=')) {
            $useless = 2;
            $skipped = 0;
            $assertions = 1;
            $I->seeInShellOutput('UselessTest: Make unexpected assertion.....................................Useless');
        } else {
            $useless = 1;
            $skipped = 1;
            $assertions = 0;
            $I->seeInShellOutput('UselessTest: Make unexpected assertion.....................................Skipped');
        }
        $I->seeInShellOutput("Skipped: $skipped. Useless: $useless");

        // $I->seeInShellOutput('UselessCept: Make no assertions............................................Useless');
        // $I->seeInShellOutput('UselessCest: Make no assertions............................................Useless');
        $I->seeInShellOutput('UselessTest: Make no assertions............................................Useless');

        $I->seeFileFound('report.xml', 'tests/_output');

        if (version_compare(\PHPUnit\Runner\Version::id(), '6.0.0', '>=')) {
            $I->seeInThisFile(
                "<testsuite name=\"unit\" tests=\"4\" assertions=\"$assertions\" errors=\"$useless\" failures=\"0\" skipped=\"$skipped\" time=\""
            );
        } else {
            $I->seeInThisFile(
                "<testsuite name=\"unit\" tests=\"4\" assertions=\"$assertions\" failures=\"0\" errors=\"2\" time=\""
            );
        }
        // $I->seeInThisFile('<testcase name="Useless"');
        // $I->seeInThisFile('<testcase name="makeNoAssertions" class="UselessCest"');
        $I->seeInThisFile('<testcase name="testMakeNoAssertions" class="UselessTest" file="');
        $I->seeInThisFile('<testcase name="testMakeUnexpectedAssertion" class="UselessTest" file="');
        $I->seeInThisFile('Risky Test');

        $I->seeFileFound('phpunit-report.xml', 'tests/_output');

        if (version_compare(\PHPUnit\Runner\Version::id(), '6.0.0', '>=')) {
            $I->seeInThisFile(
                "<testsuite name=\"unit\" tests=\"4\" assertions=\"$assertions\" errors=\"$useless\" failures=\"0\" skipped=\"$skipped\" time=\""
            );
        } else {
            $I->seeInThisFile(
                "<testsuite name=\"unit\" tests=\"4\" assertions=\"$assertions\" failures=\"0\" errors=\"2\" time=\""
            );
        }
        // $I->seeInThisFile('<testcase name="Useless"');
        // $I->seeInThisFile('<testcase name="makeNoAssertions" class="UselessCest"');
        $I->seeInThisFile('<testcase name="testMakeNoAssertions" class="UselessTest" file="');
        $I->seeInThisFile('<testcase name="testMakeUnexpectedAssertion" class="UselessTest" file="');
        $I->seeInThisFile('Risky Test');

        $I->seeFileFound('report.html', 'tests/_output');
        $I->seeInThisFile('<td class="scenarioUseless">Useless scenarios:</td>');
        $I->seeInThisFile("<td class=\"scenarioUselessValue\"><strong>$useless</strong></td>");
        $I->seeInThisFile('UselessCest');
        $I->seeInThisFile('UselessTest');
        $I->seeInThisFile('UselessCept');
    }
}
