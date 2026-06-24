<?php

declare(strict_types=1);

use Tests\Support\CliTester;

final class CodeceptionYmlInTestsDirCest
{
    public function runTestPath(CliTester $I)
    {
        $I->amInPath('tests/data/codeception_yml_in_tests_dir');
        $I->executeCommand('run unit/ExampleCest.php');

        $I->seeResultCodeIs(0);
        $I->dontSeeInShellOutput(\RuntimeException::class);
        $I->dontSeeInShellOutput('could not be found');
    }
}
