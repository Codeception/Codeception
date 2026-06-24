<?php

declare(strict_types=1);

use Codeception\Attribute\Group;
use Codeception\Scenario;
use Tests\Support\CliTester;

#[Group('core')]
final class BuildCest
{
    private string $originalCliHelperContents;

    public function _before()
    {
        $this->originalCliHelperContents = file_get_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'));
    }

    public function _after()
    {
        file_put_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'), $this->originalCliHelperContents);
    }

    public function buildsActionsForAClass(CliTester $I)
    {
        $I->wantToTest('build command');
        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CodeTester.php');
        $I->seeInSupportDir('CliTester.php');
        $I->seeInThisFile('class CliTester extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliTesterActions');
        $I->seeFileFound('CliTesterActions.php', 'tests/Support/_generated');
        $I->seeThisFileMatches("!^<\?php .*\n// phpcs:ignoreFile!");
        $I->seeInThisFile('seeFileFound(');
        // native mixed and void return type was added in codeception/lib-asserts:3
        $I->seeThisFileMatches('!public function assertSame\((mixed )?\$expected, (mixed )?\$actual, string \$message = ""\)(: void)? \{!');
    }

    public function usesLiteralTypes(CliTester $I, Scenario $scenario)
    {
        $I->wantToTest('generate typehints with generated actions');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'));

        $cliHelperContents = str_replace('public function grabFromOutput($regex)', 'public function grabFromOutput(string $regex): string', $cliHelperContents);

        file_put_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliTester.php');
        $I->seeInThisFile('class CliTester extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliTesterActions');
        $I->seeFileFound('CliTesterActions.php', 'tests/Support/_generated');
        $I->seeInThisFile('public function grabFromOutput(string $regex): string');
    }

    public function generatedUnionReturnType(CliTester $I, Scenario $scenario)
    {
        $I->wantToTest('generate action with union return type');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'));
        $cliHelperContents = str_replace('public function grabFromOutput($regex)', 'public function grabFromOutput(array|string $param): int|string', $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliTester.php');
        $I->seeInThisFile('class CliTester extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliTesterActions');
        $I->seeFileFound('CliTesterActions.php', 'tests/Support/_generated');
        $I->seeInThisFile('public function grabFromOutput(array|string $param): string|int');
    }

    public function generatedIntersectReturnTypeOnPhp81(CliTester $I, Scenario $scenario)
    {
        $I->wantToTest('generate action with intersect return type');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'));
        $cliHelperContents = str_replace('public function grabFromOutput($regex)', 'public function grabFromOutput(CliHelper&\ArrayObject $param): CliHelper&\ArrayObject', $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliTester.php');
        $I->seeInThisFile('class CliTester extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliTesterActions');
        $I->seeFileFound('CliTesterActions.php', 'tests/Support/_generated');
        $I->seeInThisFile('public function grabFromOutput(\Tests\Support\Helper\CliHelper&\ArrayObject $param): \Tests\Support\Helper\CliHelper&\ArrayObject');
    }

    public function noReturnForVoidType(CliTester $I, Scenario $scenario)
    {
        $I->wantToTest('no return keyword generated for void typehint');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', 'public function seeDirFound($dir): void', $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliTester.php');
        $I->seeInThisFile('class CliTester extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliTesterActions');
        $I->seeFileFound('CliTesterActions.php', 'tests/Support/_generated');
        $I->seeInThisFile('public function seeDirFound($dir): void');
        $I->seeInThisFile('$this->getScenario()->runStep(new \Codeception\Step\ConditionalAssertion(\'seeDirFound\', func_get_args()));');
        $I->dontSeeInThisFile('return $this->getScenario()->runStep(new \Codeception\Step\ConditionalAssertion(\'seeDirFound\', func_get_args()));');
    }

    public function generateNullableParameters(CliTester $I, Scenario $scenario)
    {
        $cliHelperContents = file_get_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', 'public function seeDirFound(?\Directory $dir = null): ?bool', $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliTester.php');
        $I->seeInThisFile('class CliTester extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliTesterActions');
        $I->seeFileFound('CliTesterActions.php', 'tests/Support/_generated');
        $I->seeInThisFile('public function seeDirFound(?\Directory $dir = NULL): ?bool');
    }

    public function generateVariadicParameters(CliTester $I, Scenario $scenario)
    {
        $cliHelperContents = file_get_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', 'public function seeDirsFound(\Directory ...$dirs): ?bool', $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliTester.php');
        $I->seeInThisFile('class CliTester extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliTesterActions');
        $I->seeFileFound('CliTesterActions.php', 'tests/Support/_generated');
        $I->seeInThisFile('public function seeDirsFound(\Directory ...$dirs): ?bool');
    }

    public function generateMixedParameters(CliTester $I, Scenario $scenario)
    {
        $cliHelperContents = file_get_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', 'public function seeDirFound(mixed $dir = null): mixed', $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliTester.php');
        $I->seeInThisFile('class CliTester extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliTesterActions');
        $I->seeFileFound('CliTesterActions.php', 'tests/Support/_generated');
        $I->seeInThisFile('public function seeDirFound(mixed $dir = NULL): mixed');
    }

    public function generateCorrectTypeWhenSelfTypeIsUsed(CliTester $I, Scenario $scenario)
    {
        $cliHelperContents = file_get_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', 'public function seeDirFound(self $dir): self', $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliTester.php');
        $I->seeInThisFile('class CliTester extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliTesterActions');
        $I->seeFileFound('CliTesterActions.php', 'tests/Support/_generated');
        $I->seeInThisFile('public function seeDirFound(\Tests\Support\Helper\CliHelper $dir): \Tests\Support\Helper\CliHelper');
    }

    public function generateCorrectTypeWhenParentTypeIsUsed(CliTester $I, Scenario $scenario)
    {
        $cliHelperContents = file_get_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', 'public function seeDirFound(parent $dir): parent', $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliTester.php');
        $I->seeInThisFile('class CliTester extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliTesterActions');
        $I->seeFileFound('CliTesterActions.php', 'tests/Support/_generated');
        $I->seeInThisFile('public function seeDirFound(\Codeception\Module $dir): \Codeception\Module');
    }

    public function noReturnForNeverType(CliTester $I, Scenario $scenario)
    {
        $I->wantToTest('no return keyword generated for never typehint');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', 'public function seeDirFound($dir): never', $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliTester.php');
        $I->seeInThisFile('class CliTester extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliTesterActions');
        $I->seeFileFound('CliTesterActions.php', 'tests/Support/_generated');
        $I->seeInThisFile('public function seeDirFound($dir): never');
        $I->seeInThisFile('$this->getScenario()->runStep(new \Codeception\Step\ConditionalAssertion(\'seeDirFound\', func_get_args()));');
        $I->dontSeeInThisFile('return $this->getScenario()->runStep(new \Codeception\Step\ConditionalAssertion(\'seeDirFound\', func_get_args()));');
    }

    public function generateAttributeForMethodAttributeWithNoParametersAndNoBrackets(CliTester $I, Scenario $scenario)
    {
        $I->wantToTest('attribute generation for method attribute with no parameters and no brackets');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', "#[\Codeception\Attribute\Examples]\n    public function seeDirFound(\$dir)", $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliTester.php');
        $I->seeInThisFile('class CliTester extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliTesterActions');
        $I->seeFileFound('CliTesterActions.php', 'tests/Support/_generated');
        $I->seeInThisFile("*/\n    #[\Codeception\Attribute\Examples()]\n    public function seeDirFound(\$dir) {");
        $I->seeInThisFile("*/\n    #[\Codeception\Attribute\Examples()]\n    public function canSeeDirFound(\$dir) {");
    }

    public function generateAttributeForMethodAttributeWithNoParametersAndEmptyBrackets(CliTester $I, Scenario $scenario)
    {
        $I->wantToTest('attribute generation for method attribute with no parameters and empty brackets');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', "#[\Codeception\Attribute\Examples()]\n    public function seeDirFound(\$dir)", $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliTester.php');
        $I->seeInThisFile('class CliTester extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliTesterActions');
        $I->seeFileFound('CliTesterActions.php', 'tests/Support/_generated');
        $I->seeInThisFile("*/\n    #[\Codeception\Attribute\Examples()]\n    public function seeDirFound(\$dir) {");
        $I->seeInThisFile("*/\n    #[\Codeception\Attribute\Examples()]\n    public function canSeeDirFound(\$dir) {");
    }

    public function generateAttributeForMethodAttributeWithASingleParameter(CliTester $I, Scenario $scenario)
    {
        $I->wantToTest('attribute generation for method attribute with a single parameter');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', "#[\Codeception\Attribute\Examples('magic')]\n    public function seeDirFound(\$dir)", $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliTester.php');
        $I->seeInThisFile('class CliTester extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliTesterActions');
        $I->seeFileFound('CliTesterActions.php', 'tests/Support/_generated');
        $I->seeInThisFile("*/\n    #[\Codeception\Attribute\Examples(\"magic\")]\n    public function seeDirFound(\$dir) {");
        $I->seeInThisFile("*/\n    #[\Codeception\Attribute\Examples(\"magic\")]\n    public function canSeeDirFound(\$dir) {");
    }

    public function generateAttributeForMethodAttributeWithMultipleParameters(CliTester $I, Scenario $scenario)
    {
        $I->wantToTest('attribute generation for method attribute with multiple parameters');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', "#[\Codeception\Attribute\Examples('magic', \"dark\")]\n    public function seeDirFound(\$dir)", $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliTester.php');
        $I->seeInThisFile('class CliTester extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliTesterActions');
        $I->seeFileFound('CliTesterActions.php', 'tests/Support/_generated');
        $I->seeInThisFile("*/\n    #[\Codeception\Attribute\Examples(\"magic\", \"dark\")]\n    public function seeDirFound(\$dir) {");
        $I->seeInThisFile("*/\n    #[\Codeception\Attribute\Examples(\"magic\", \"dark\")]\n    public function canSeeDirFound(\$dir) {");
    }

    public function generateAttributeForMethodAttributeWithMultipleParametersOverMultipleLines(CliTester $I, Scenario $scenario)
    {
        $I->wantToTest('attribute generation for method attribute with multiple parameters over multiple lines');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', "#[\Codeception\Attribute\Examples(\n'magic',\n\"dark\")]\n    public function seeDirFound(\$dir)", $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliTester.php');
        $I->seeInThisFile('class CliTester extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliTesterActions');
        $I->seeFileFound('CliTesterActions.php', 'tests/Support/_generated');
        $I->seeInThisFile("*/\n    #[\Codeception\Attribute\Examples(\"magic\", \"dark\")]\n    public function seeDirFound(\$dir) {");
        $I->seeInThisFile("*/\n    #[\Codeception\Attribute\Examples(\"magic\", \"dark\")]\n    public function canSeeDirFound(\$dir) {");
    }

    public function generateAttributesForMultipleMethodAttributeDeclarations(CliTester $I, Scenario $scenario)
    {
        $I->wantToTest('attribute generation for multiple method attribute declarations');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', "#[\Codeception\Attribute\Examples()]\n   #[\Codeception\Attribute\Before(\"doX\")]\n   public function seeDirFound(\$dir)", $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliTester.php');
        $I->seeInThisFile('class CliTester extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliTesterActions');
        $I->seeFileFound('CliTesterActions.php', 'tests/Support/_generated');
        $I->seeInThisFile("*/\n    #[\Codeception\Attribute\Examples()]\n    #[\Codeception\Attribute\Before(\"doX\")]\n    public function seeDirFound(\$dir) {");
        $I->seeInThisFile("*/\n    #[\Codeception\Attribute\Examples()]\n    #[\Codeception\Attribute\Before(\"doX\")]\n    public function canSeeDirFound(\$dir) {");
    }

    public function generateAttributeForParameterAttributeWithNoParametersAndNoBrackets(CliTester $I, Scenario $scenario)
    {
        $I->wantToTest('attribute generation for parameter attribute with no parameters and no brackets');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', "public function seeDirFound(#[\JetBrains\PhpStorm\Deprecated] \$dir)", $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliTester.php');
        $I->seeInThisFile('class CliTester extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliTesterActions');
        $I->seeFileFound('CliTesterActions.php', 'tests/Support/_generated');
        $I->seeInThisFile("*/\n    public function seeDirFound(#[\JetBrains\PhpStorm\Deprecated()] \$dir) {");
        $I->seeInThisFile("*/\n    public function canSeeDirFound(#[\JetBrains\PhpStorm\Deprecated()] \$dir) {");
    }

    public function generateAttributeForParameterAttributeWithMultipleParameters(CliTester $I, Scenario $scenario)
    {
        $I->wantToTest('attribute generation for parameter attribute with multiple parameters');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', "public function seeDirFound(#[\JetBrains\PhpStorm\Deprecated(\"it's old\", 'see a better method')] \$dir)", $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliTester.php');
        $I->seeInThisFile('class CliTester extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliTesterActions');
        $I->seeFileFound('CliTesterActions.php', 'tests/Support/_generated');
        $I->seeInThisFile("*/\n    public function seeDirFound(#[\JetBrains\PhpStorm\Deprecated(\"it's old\", \"see a better method\")] \$dir) {");
        $I->seeInThisFile("*/\n    public function canSeeDirFound(#[\JetBrains\PhpStorm\Deprecated(\"it's old\", \"see a better method\")] \$dir) {");
    }

    public function generateAttributesForParameterAttributesWithMultipleAttributeDeclarations(CliTester $I, Scenario $scenario)
    {
        $I->wantToTest('attribute generation for parameter attribute with multiple attribute declarations');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', "public function seeDirFound(#[\JetBrains\PhpStorm\Deprecated(\"it's old\")] #[\JetBrains\PhpStorm\ExpectedValues([ 1, 2 ])] \$dir)", $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliTester.php');
        $I->seeInThisFile('class CliTester extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliTesterActions');
        $I->seeFileFound('CliTesterActions.php', 'tests/Support/_generated');
        $I->seeInThisFile("*/\n    public function seeDirFound(#[\JetBrains\PhpStorm\Deprecated(\"it's old\")]#[\JetBrains\PhpStorm\ExpectedValues([1, 2])] \$dir) {");
        $I->seeInThisFile("*/\n    public function canSeeDirFound(#[\JetBrains\PhpStorm\Deprecated(\"it's old\")]#[\JetBrains\PhpStorm\ExpectedValues([1, 2])] \$dir) {");
    }

    public function generateAttributesForParameterAttributesWithMultipleAttributesInASingleDeclaration(CliTester $I, Scenario $scenario)
    {
        $I->wantToTest('attribute generation for parameter attribute with multiple attributes in a single declaration');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', "public function seeDirFound(#[\JetBrains\PhpStorm\Deprecated(\"it's old\"), \JetBrains\PhpStorm\ExpectedValues([ 1, 2 ])] \$dir)", $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/Support/Helper/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliTester.php');
        $I->seeInThisFile('class CliTester extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliTesterActions');
        $I->seeFileFound('CliTesterActions.php', 'tests/Support/_generated');
        $I->seeInThisFile("*/\n    public function seeDirFound(#[\JetBrains\PhpStorm\Deprecated(\"it's old\")]#[\JetBrains\PhpStorm\ExpectedValues([1, 2])] \$dir) {");
        $I->seeInThisFile("*/\n    public function canSeeDirFound(#[\JetBrains\PhpStorm\Deprecated(\"it's old\")]#[\JetBrains\PhpStorm\ExpectedValues([1, 2])] \$dir) {");
    }
}
