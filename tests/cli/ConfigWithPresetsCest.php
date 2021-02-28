<?php

declare(strict_types=1);

final class ConfigWithPresetsCest
{
    public function loadWithPresets(CliGuy $I)
    {
        $I->amInPath('tests/data/presets');
        $I->executeCommand('run -c codeception.yml');
        $I->seeInShellOutput('OK (1 test');
    }
}
