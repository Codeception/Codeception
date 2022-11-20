<?php

declare(strict_types=1);

use Codeception\Attribute\Group;
use Codeception\Scenario;

#[Group('core')]
final class BuildCest
{
    /** @var string */
    private string $originalCliHelperContents;

    public function _before()
    {
        $this->originalCliHelperContents = file_get_contents(codecept_root_dir('tests/support/CliHelper.php'));
    }

    public function _after()
    {
        file_put_contents(codecept_root_dir('tests/support/CliHelper.php'), $this->originalCliHelperContents);
    }

    public function buildsActionsForAClass(CliGuy $I): void
    {
        $I->wantToTest('build command');
        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CodeGuy.php');
        $I->seeInSupportDir('CliGuy.php');
        $I->seeInThisFile('class CliGuy extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliGuyActions');
        $I->seeFileFound('CliGuyActions.php', 'tests/support/_generated');
        $I->seeInThisFile('seeFileFound(');
        $I->seeInThisFile('public function assertSame($expected, $actual, string $message = "") {');
    }

    public function usesLiteralTypes(CliGuy $I, Scenario $scenario): void
    {
        $I->wantToTest('generate typehints with generated actions');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/support/CliHelper.php'));

        $cliHelperContents = str_replace('public function grabFromOutput($regex)', 'public function grabFromOutput(string $regex): string', $cliHelperContents);

        file_put_contents(codecept_root_dir('tests/support/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliGuy.php');
        $I->seeInThisFile('class CliGuy extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliGuyActions');
        $I->seeFileFound('CliGuyActions.php', 'tests/support/_generated');
        $I->seeInThisFile('public function grabFromOutput(string $regex): string');
    }

    public function generatedUnionReturnType(CliGuy $I, Scenario $scenario): void
    {
        $I->wantToTest('generate action with union return type');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/support/CliHelper.php'));
        $cliHelperContents = str_replace('public function grabFromOutput($regex)', 'public function grabFromOutput(array|string $param): int|string', $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/support/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliGuy.php');
        $I->seeInThisFile('class CliGuy extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliGuyActions');
        $I->seeFileFound('CliGuyActions.php', 'tests/support/_generated');
        $I->seeInThisFile('public function grabFromOutput(array|string $param): string|int');
    }

    public function generatedIntersectReturnTypeOnPhp81(CliGuy $I, Scenario $scenario): void
    {
        if (PHP_VERSION_ID < 80100) {
            $scenario->skip('Does not work in PHP < 8.1');
        }

        $I->wantToTest('generate action with intersect return type');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/support/CliHelper.php'));
        $cliHelperContents = str_replace('public function grabFromOutput($regex)', 'public function grabFromOutput(CliHelper&\ArrayObject $param): CliHelper&\ArrayObject', $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/support/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliGuy.php');
        $I->seeInThisFile('class CliGuy extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliGuyActions');
        $I->seeFileFound('CliGuyActions.php', 'tests/support/_generated');
        $I->seeInThisFile('public function grabFromOutput(\Codeception\Module\CliHelper&\ArrayObject $param): \Codeception\Module\CliHelper&\ArrayObject');
    }

    public function noReturnForVoidType(CliGuy $I, Scenario $scenario): void
    {
        $I->wantToTest('no return keyword generated for void typehint');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/support/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', 'public function seeDirFound($dir): void', $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/support/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliGuy.php');
        $I->seeInThisFile('class CliGuy extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliGuyActions');
        $I->seeFileFound('CliGuyActions.php', 'tests/support/_generated');
        $I->seeInThisFile('public function seeDirFound($dir): void');
        $I->seeInThisFile('$this->getScenario()->runStep(new \Codeception\Step\ConditionalAssertion(\'seeDirFound\', func_get_args()));');
        $I->dontSeeInThisFile('return $this->getScenario()->runStep(new \Codeception\Step\ConditionalAssertion(\'seeDirFound\', func_get_args()));');
    }

    public function generateNullableParameters(CliGuy $I, Scenario $scenario): void
    {
        $cliHelperContents = file_get_contents(codecept_root_dir('tests/support/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', 'public function seeDirFound(\Directory $dir = null): ?bool', $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/support/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliGuy.php');
        $I->seeInThisFile('class CliGuy extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliGuyActions');
        $I->seeFileFound('CliGuyActions.php', 'tests/support/_generated');
        $I->seeInThisFile('public function seeDirFound(?\Directory $dir = NULL): ?bool');
    }

    public function generateMixedParameters(CliGuy $I, Scenario $scenario): void
    {
        $cliHelperContents = file_get_contents(codecept_root_dir('tests/support/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', 'public function seeDirFound(mixed $dir = null): mixed', $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/support/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliGuy.php');
        $I->seeInThisFile('class CliGuy extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliGuyActions');
        $I->seeFileFound('CliGuyActions.php', 'tests/support/_generated');
        $I->seeInThisFile('public function seeDirFound(mixed $dir = NULL): mixed');
    }

    public function generateCorrectTypeWhenSelfTypeIsUsed(CliGuy $I, Scenario $scenario): void
    {
        if (PHP_MAJOR_VERSION < 7) {
            $scenario->skip('Does not work in PHP < 7');
        }
        $cliHelperContents = file_get_contents(codecept_root_dir('tests/support/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', 'public function seeDirFound(self $dir): self', $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/support/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliGuy.php');
        $I->seeInThisFile('class CliGuy extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliGuyActions');
        $I->seeFileFound('CliGuyActions.php', 'tests/support/_generated');
        $I->seeInThisFile('public function seeDirFound(\Codeception\Module\CliHelper $dir): \Codeception\Module\CliHelper');
    }

    public function generateCorrectTypeWhenParentTypeIsUsed(CliGuy $I, Scenario $scenario): void
    {
        if (PHP_MAJOR_VERSION < 7) {
            $scenario->skip('Does not work in PHP < 7');
        }
        $cliHelperContents = file_get_contents(codecept_root_dir('tests/support/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', 'public function seeDirFound(parent $dir): parent', $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/support/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliGuy.php');
        $I->seeInThisFile('class CliGuy extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliGuyActions');
        $I->seeFileFound('CliGuyActions.php', 'tests/support/_generated');
        $I->seeInThisFile('public function seeDirFound(\Codeception\Module $dir): \Codeception\Module');
    }

    public function noReturnForNeverType(CliGuy $I, Scenario $scenario): void
    {
        if (PHP_VERSION_ID < 80100) {
            $scenario->skip('Does not work in PHP < 8.1');
        }

        $I->wantToTest('no return keyword generated for never typehint');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/support/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', 'public function seeDirFound($dir): never', $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/support/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliGuy.php');
        $I->seeInThisFile('class CliGuy extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliGuyActions');
        $I->seeFileFound('CliGuyActions.php', 'tests/support/_generated');
        $I->seeInThisFile('public function seeDirFound($dir): never');
        $I->seeInThisFile('$this->getScenario()->runStep(new \Codeception\Step\ConditionalAssertion(\'seeDirFound\', func_get_args()));');
        $I->dontSeeInThisFile('return $this->getScenario()->runStep(new \Codeception\Step\ConditionalAssertion(\'seeDirFound\', func_get_args()));');
    }

    public function generateAttributeForMethodAttributeWithNoParametersAndNoBrackets(CliGuy $I, Scenario $scenario): void
    {
        $I->wantToTest('attribute generation for method attribute with no parameters and no brackets');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/support/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', "#[\Codeception\Attribute\Examples]\n    public function seeDirFound(\$dir)", $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/support/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliGuy.php');
        $I->seeInThisFile('class CliGuy extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliGuyActions');
        $I->seeFileFound('CliGuyActions.php', 'tests/support/_generated');
        $I->seeInThisFile("*/\n    #[\Codeception\Attribute\Examples()]\n    public function seeDirFound(\$dir) {");
        $I->seeInThisFile("*/\n    #[\Codeception\Attribute\Examples()]\n    public function canSeeDirFound(\$dir) {");
    }

    public function generateAttributeForMethodAttributeWithNoParametersAndEmptyBrackets(CliGuy $I, Scenario $scenario): void
    {
        $I->wantToTest('attribute generation for method attribute with no parameters and empty brackets');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/support/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', "#[\Codeception\Attribute\Examples()]\n    public function seeDirFound(\$dir)", $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/support/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliGuy.php');
        $I->seeInThisFile('class CliGuy extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliGuyActions');
        $I->seeFileFound('CliGuyActions.php', 'tests/support/_generated');
        $I->seeInThisFile("*/\n    #[\Codeception\Attribute\Examples()]\n    public function seeDirFound(\$dir) {");
        $I->seeInThisFile("*/\n    #[\Codeception\Attribute\Examples()]\n    public function canSeeDirFound(\$dir) {");
    }

    public function generateAttributeForMethodAttributeWithASingleParameter(CliGuy $I, Scenario $scenario): void
    {
        $I->wantToTest('attribute generation for method attribute with a single parameter');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/support/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', "#[\Codeception\Attribute\Examples('magic')]\n    public function seeDirFound(\$dir)", $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/support/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliGuy.php');
        $I->seeInThisFile('class CliGuy extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliGuyActions');
        $I->seeFileFound('CliGuyActions.php', 'tests/support/_generated');
        $I->seeInThisFile("*/\n    #[\Codeception\Attribute\Examples(\"magic\")]\n    public function seeDirFound(\$dir) {");
        $I->seeInThisFile("*/\n    #[\Codeception\Attribute\Examples(\"magic\")]\n    public function canSeeDirFound(\$dir) {");
    }

    public function generateAttributeForMethodAttributeWithMultipleParameters(CliGuy $I, Scenario $scenario): void
    {
        $I->wantToTest('attribute generation for method attribute with multiple parameters');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/support/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', "#[\Codeception\Attribute\Examples('magic', \"dark\")]\n    public function seeDirFound(\$dir)", $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/support/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliGuy.php');
        $I->seeInThisFile('class CliGuy extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliGuyActions');
        $I->seeFileFound('CliGuyActions.php', 'tests/support/_generated');
        $I->seeInThisFile("*/\n    #[\Codeception\Attribute\Examples(\"magic\", \"dark\")]\n    public function seeDirFound(\$dir) {");
        $I->seeInThisFile("*/\n    #[\Codeception\Attribute\Examples(\"magic\", \"dark\")]\n    public function canSeeDirFound(\$dir) {");
    }

    public function generateAttributeForMethodAttributeWithMultipleParametersOverMultipleLines(CliGuy $I, Scenario $scenario): void
    {
        $I->wantToTest('attribute generation for method attribute with multiple parameters over multiple lines');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/support/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', "#[\Codeception\Attribute\Examples(\n'magic',\n\"dark\")]\n    public function seeDirFound(\$dir)", $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/support/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliGuy.php');
        $I->seeInThisFile('class CliGuy extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliGuyActions');
        $I->seeFileFound('CliGuyActions.php', 'tests/support/_generated');
        $I->seeInThisFile("*/\n    #[\Codeception\Attribute\Examples(\"magic\", \"dark\")]\n    public function seeDirFound(\$dir) {");
        $I->seeInThisFile("*/\n    #[\Codeception\Attribute\Examples(\"magic\", \"dark\")]\n    public function canSeeDirFound(\$dir) {");
    }

    public function generateAttributesForMultipleMethodAttributeDeclarations(CliGuy $I, Scenario $scenario): void
    {
        $I->wantToTest('attribute generation for multiple method attribute declarations');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/support/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', "#[\Codeception\Attribute\Examples()]\n   #[\Codeception\Attribute\Before(\"doX\")]\n   public function seeDirFound(\$dir)", $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/support/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliGuy.php');
        $I->seeInThisFile('class CliGuy extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliGuyActions');
        $I->seeFileFound('CliGuyActions.php', 'tests/support/_generated');
        $I->seeInThisFile("*/\n    #[\Codeception\Attribute\Examples()]\n    #[\Codeception\Attribute\Before(\"doX\")]\n    public function seeDirFound(\$dir) {");
        $I->seeInThisFile("*/\n    #[\Codeception\Attribute\Examples()]\n    #[\Codeception\Attribute\Before(\"doX\")]\n    public function canSeeDirFound(\$dir) {");
    }

    public function generateAttributeForParameterAttributeWithNoParametersAndNoBrackets(CliGuy $I, Scenario $scenario): void
    {
        $I->wantToTest('attribute generation for parameter attribute with no parameters and no brackets');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/support/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', "public function seeDirFound(#[\JetBrains\PhpStorm\Deprecated] \$dir)", $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/support/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliGuy.php');
        $I->seeInThisFile('class CliGuy extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliGuyActions');
        $I->seeFileFound('CliGuyActions.php', 'tests/support/_generated');
        $I->seeInThisFile("*/\n    public function seeDirFound(#[\JetBrains\PhpStorm\Deprecated()] \$dir) {");
        $I->seeInThisFile("*/\n    public function canSeeDirFound(#[\JetBrains\PhpStorm\Deprecated()] \$dir) {");
    }

    public function generateAttributeForParameterAttributeWithMultipleParameters(CliGuy $I, Scenario $scenario): void
    {
        $I->wantToTest('attribute generation for parameter attribute with multiple parameters');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/support/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', "public function seeDirFound(#[\JetBrains\PhpStorm\Deprecated(\"it's old\", 'see a better method')] \$dir)", $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/support/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliGuy.php');
        $I->seeInThisFile('class CliGuy extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliGuyActions');
        $I->seeFileFound('CliGuyActions.php', 'tests/support/_generated');
        $I->seeInThisFile("*/\n    public function seeDirFound(#[\JetBrains\PhpStorm\Deprecated(\"it's old\", \"see a better method\")] \$dir) {");
        $I->seeInThisFile("*/\n    public function canSeeDirFound(#[\JetBrains\PhpStorm\Deprecated(\"it's old\", \"see a better method\")] \$dir) {");
    }

    public function generateAttributesForParameterAttributesWithMultipleAttributeDeclarations(CliGuy $I, Scenario $scenario): void
    {
        $I->wantToTest('attribute generation for parameter attribute with multiple attribute declarations');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/support/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', "public function seeDirFound(#[\JetBrains\PhpStorm\Deprecated(\"it's old\")] #[\JetBrains\PhpStorm\ExpectedValues([ 1, 2 ])] \$dir)", $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/support/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliGuy.php');
        $I->seeInThisFile('class CliGuy extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliGuyActions');
        $I->seeFileFound('CliGuyActions.php', 'tests/support/_generated');
        $I->seeInThisFile("*/\n    public function seeDirFound(#[\JetBrains\PhpStorm\Deprecated(\"it's old\")]#[\JetBrains\PhpStorm\ExpectedValues([1, 2])] \$dir) {");
        $I->seeInThisFile("*/\n    public function canSeeDirFound(#[\JetBrains\PhpStorm\Deprecated(\"it's old\")]#[\JetBrains\PhpStorm\ExpectedValues([1, 2])] \$dir) {");
    }

    public function generateAttributesForParameterAttributesWithMultipleAttributesInASingleDeclaration(CliGuy $I, Scenario $scenario): void
    {
        $I->wantToTest('attribute generation for parameter attribute with multiple attributes in a single declaration');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/support/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', "public function seeDirFound(#[\JetBrains\PhpStorm\Deprecated(\"it's old\"), \JetBrains\PhpStorm\ExpectedValues([ 1, 2 ])] \$dir)", $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/support/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliGuy.php');
        $I->seeInThisFile('class CliGuy extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliGuyActions');
        $I->seeFileFound('CliGuyActions.php', 'tests/support/_generated');
        $I->seeInThisFile("*/\n    public function seeDirFound(#[\JetBrains\PhpStorm\Deprecated(\"it's old\")]#[\JetBrains\PhpStorm\ExpectedValues([1, 2])] \$dir) {");
        $I->seeInThisFile("*/\n    public function canSeeDirFound(#[\JetBrains\PhpStorm\Deprecated(\"it's old\")]#[\JetBrains\PhpStorm\ExpectedValues([1, 2])] \$dir) {");
    }
}
