<?php
/**
 * @group gherkin
 */
class GherkinCest
{
    public function _before(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
    }

    public function steps(CliGuy $I)
    {
        $I->executeCommand('gherkin:steps scenario');
        $I->seeInShellOutput('I have terminal opened');
        $I->seeInShellOutput('ScenarioGuy::terminal');
        $I->seeInShellOutput('there is a file :name');
        $I->seeInShellOutput('I see file :name');
        $I->seeInShellOutput('ScenarioGuy::matchFile');
    }

    public function snippets(CliGuy $I)
    {
        $I->executeCommand('gherkin:snippets scenario');
        $I->seeInShellOutput('@Given I have only idea of what\'s going on here');
        $I->seeInShellOutput('public function iHaveOnlyIdeaOfWhatsGoingOnHere');
    }

    public function snippetsScenarioFile(CliGuy $I)
    {
        $I->executeCommand('gherkin:snippets scenario FileExamples.feature');
        $I->dontSeeInShellOutput('@Given I have only idea of what\'s going on here');
        $I->dontSeeInShellOutput('public function iHaveOnlyIdeaOfWhatsGoingOnHere');
    }

    public function snippetsScenarioFolder(CliGuy $I)
    {
        $I->executeCommand('gherkin:snippets scenario subfolder');
        $I->seeInShellOutput('Given I have a feature in a subfolder');
        $I->seeInShellOutput('public function iHaveAFeatureInASubfolder');
        $I->dontSeeInShellOutput('@Given I have only idea of what\'s going on here');
        $I->dontSeeInShellOutput('public function iHaveOnlyIdeaOfWhatsGoingOnHere');
    }

    public function snippetsPyStringArgument(CliGuy $I)
    {
        $I->executeCommand('gherkin:snippets scenario PyStringArgumentExample.feature');
        $I->seeInShellOutput('@Given I have PyString argument :arg1');
        $I->seeInShellOutput('public function iHavePyStringArgument($arg1)');
        $I->dontSeeInShellOutput('public function iSeeOutput($arg1)');
    }

    public function runIncompletedStepWithPyStringArgument(CliGuy $I)
    {
        $I->executeCommand('run scenario "PyStringArgumentExample.feature:PyString argument" --steps');
        $I->seeInShellOutput('Step definition for `I have PyString argument ""` not found in contexts');
        $I->dontSeeInShellOutput('Step definition for `I see output` not found in contexts');
    }

    public function runSameStepWithInlineAndPyStringArgument(CliGuy $I)
    {
        $I->executeCommand('run scenario "InlineArgumentExample.feature:Running step with inline argument" --steps');
        $I->seeInShellOutput("Argument: test");

        $I->executeCommand('run scenario "PyStringArgumentExample.feature:Running step with PyString argument" --steps');
        $I->seeInShellOutput("Argument: First line\nSecond line");
    }

    public function snippetsScenarioUtf8(CliGuy $I)
    {
        $I->executeCommand('gherkin:snippets scenario Utf8Example.feature');
        $I->seeInShellOutput('@Given я написал сценарий на языке :arg1');
        $I->seeInShellOutput('public function step_62e20dc62($arg1)');
    }
}
