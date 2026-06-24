<?php

declare(strict_types=1);

use Tests\Support\CliTester;

final class ConfigExtendsCest
{
    public function runIncludedSuites(CliTester $I)
    {
        $I->amInPath('tests/data/config_extends');
        $I->executeCommand('run');

        $I->seeInShellOutput('UnitCest');
        $I->seeInShellOutput('OK (1 test, 1 assertion)');
        $I->dontSeeInShellOutput('Exception');
    }
}
