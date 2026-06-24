<?php

declare(strict_types=1);

use Tests\Support\CliTester;

final class ConfigWithPresetsCest
{
    public function loadWithPresets(CliTester $I)
    {
        $I->amInPath('tests/data/presets');
        $I->executeCommand('run -c codeception.yml');
        $I->seeInShellOutput('OK (1 test');
    }
}
