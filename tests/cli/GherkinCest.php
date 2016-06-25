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
        $I->seeInShellOutput('ScenarioGuy::matchFile');
    }

    public function snippets(CliGuy $I)
    {
        $I->executeCommand('gherkin:snippets scenario');
        $I->seeInShellOutput('@Given I have only idea of what\'s going on here');
        $I->seeInShellOutput('public function iHaveOnlyIdeaOfWhatsGoingOnHere');
    }

    public function snippetsScenario(CliGuy $I)
    {
        $I->executeCommand('gherkin:snippets scenario FileExamples.feature');
        $I->dontSeeInShellOutput('@Given I have only idea of what\'s going on here');
        $I->dontSeeInShellOutput('public function iHaveOnlyIdeaOfWhatsGoingOnHere');
    }    
}
