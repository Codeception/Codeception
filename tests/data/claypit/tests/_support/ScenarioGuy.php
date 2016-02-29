<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
*/
class ScenarioGuy extends \Codeception\Actor
{
    use _generated\ScenarioGuyActions;

    public function seeCodeCoverageFilesArePresent()
    {
        $this->seeFileFound('c3.php');
        $this->seeFileFound('composer.json');
        $this->seeInThisFile('codeception/c3');
    }

    /**
     * @Given I have terminal opened
     */
    public function terminal()
    {
        $this->comment('I am terminal user!');
    }

    /**
     * @When I am in current directory
     */
    public function openCurrentDir()
    {
        $this->amInPath('.');
    }

    /**
     * @Given I am inside :dir
     */
    public function openDir($path)
    {
        $this->amInPath($path);
    }

    /**
     * @Then there is a file :name
     */
    public function matchFile($name)
    {
        $this->seeFileFound($name);
    }

    /**
     * @Then there are keywords in :smth
     */
    public function thereAreValues($file, \Behat\Gherkin\Node\TableNode $node)
    {
        $this->seeFileFound($file);
        foreach ($node->getRows() as $row) {
            $this->seeThisFileMatches('~' . implode('.*?', $row) . '~');
        }
    }
}
