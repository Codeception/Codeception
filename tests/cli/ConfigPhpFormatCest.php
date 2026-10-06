<?php

declare(strict_types=1);

use Tests\Support\CliTester;

final class ConfigPhpFormatCest
{
    public function _before(CliTester $I)
    {
        $I->amInPath('tests/data/sandbox');
    }

    public function validatesPhpGlobalConfig(CliTester $I)
    {
        $I->executeCommand('config:validate -c php_config/codeception.php --no-ansi', false);
        $I->dontSeeInShellOutput('ConfigurationException');
        $I->seeInShellOutput('tests => tests');
        $I->seeInShellOutput('FROM_PHP');
    }

    public function discoversAndValidatesPhpSuiteConfig(CliTester $I)
    {
        $I->executeCommand('config:validate Sample -c php_config/codeception.php --no-ansi', false);
        $I->dontSeeInShellOutput('ConfigurationException');
        $I->seeInShellOutput('Asserts');
    }

    public function phpConfigWinsOverYamlOnDiscovery(CliTester $I)
    {
        $I->executeCommand('config:validate -c php_config --no-ansi', false);
        $I->seeInShellOutput('FROM_PHP');
        $I->dontSeeInShellOutput('FROM_YAML');
    }

    public function reportsInvalidPhpConfigCleanly(CliTester $I)
    {
        $I->executeCommand('config:validate -c php_config/codeception_invalid.php --no-ansi', false);
        $I->seeInShellOutput('must return an array');
        $I->seeInShellOutput('codeception_invalid.php');
    }

    public function runsTestsThroughPhpConfig(CliTester $I)
    {
        $I->executeCommand('build -c php_config/codeception.php --no-ansi', false);
        $I->executeCommand('run Sample -c php_config/codeception.php --no-ansi', false);
        $I->seeInShellOutput('OK (1 test');
        $I->dontSeeInShellOutput('ConfigurationException');
    }

    public function phpEnvConfigWinsOverYaml(CliTester $I)
    {
        $I->executeCommand('config:validate Sample -c php_config/codeception.php --no-ansi', false);
        $I->seeInShellOutput('ENV_FROM_PHP');
        $I->dontSeeInShellOutput('ENV_FROM_YAML');
    }

    public function reportsWrongBuilderTypeCleanly(CliTester $I)
    {
        $I->executeCommand('config:validate -c php_config/codeception_wrongtype.php --no-ansi', false);
        $I->seeInShellOutput('must return a Codeception\Config\GlobalConfig instance');
        $I->seeInShellOutput('SuiteConfig');
    }

    public function swallowsOutputEmittedByConfigFile(CliTester $I)
    {
        $I->executeCommand('config:validate -c php_config/codeception_echo.php --no-ansi', false);
        $I->dontSeeInShellOutput('LEAK_OUTPUT_MARKER');
        $I->seeInShellOutput('FROM_PHP');
    }

    public function supportsCrossFormatExtends(CliTester $I)
    {
        $I->executeCommand('config:validate -c php_config/codeception_extends.php --no-ansi', false);
        $I->dontSeeInShellOutput('ConfigurationException');
        $I->seeInShellOutput('fromPreset');
    }

    public function rejectsCrossTypeExtendsPreset(CliTester $I)
    {
        $I->executeCommand('config:validate -c php_config/codeception_badextends.php --no-ansi', false);
        $I->seeInShellOutput('got Codeception\Config\SuiteConfig');
    }

    public function reportsLoadedConfigFile(CliTester $I)
    {
        $I->executeCommand('config:validate -c php_config/codeception.php --no-ansi', false);
        $I->seeInShellOutput('Loaded config file');
        $I->seeInShellOutput('codeception.php');
    }

    public function migratesYamlProjectToPhpAndRuns(CliTester $I)
    {
        $I->executeCommand('config:to-php php_migrate --no-ansi', false);
        $I->seeInShellOutput('Created');
        $I->seeFileFound('Sample.suite.php', 'php_migrate/tests');
        $I->seeFileFound('codeception.php', 'php_migrate');
        $I->seeInThisFile('GlobalConfig::create()');
        $I->seeInThisFile('->merge(');
        $I->dontSeeInThisFile('->env(');

        $I->executeCommand('build -c php_migrate/codeception.php --no-ansi', false);
        $I->executeCommand('run Sample -c php_migrate/codeception.php --no-ansi', false);
        $I->seeInShellOutput('OK (1 test');
        $I->dontSeeInShellOutput('ConfigurationException');
    }

    public function migratesDistOnlyProject(CliTester $I)
    {
        $I->executeCommand('config:to-php php_dist_only --dry-run --no-ansi', false);
        $I->dontSeeInShellOutput('Global config not found');
        $I->seeInShellOutput('GlobalConfig::create()');
        $I->seeInShellOutput('SuiteConfig::create()');
    }

    public function generateEnvironmentRefusesToShadowExistingYaml(CliTester $I)
    {
        $I->amInPath('php_config');
        $I->executeCommand('generate:environment legacy --no-ansi', false);
        $I->seeInShellOutput('shadow');
        $I->dontSeeFileFound('legacy.php', 'tests/_envs');
    }

    public function inlineSuiteShadowsSuiteFile(CliTester $I)
    {
        $I->executeCommand('config:validate Sample -c php_config/codeception_singlefile.php --no-ansi', false);
        $I->dontSeeInShellOutput('ConfigurationException');
        $I->seeInShellOutput('INLINE_WINS');
    }

    public function migrationDryRunPrintsBuilderWithoutWriting(CliTester $I)
    {
        $I->executeCommand('config:to-php php_config --dry-run --no-ansi', false);
        $I->seeInShellOutput('GlobalConfig::create()');
        $I->seeInShellOutput("->namespace('PhpConfig')");
    }
}
