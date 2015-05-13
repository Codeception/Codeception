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

    function seeCodeCoverageFilesArePresent()
    {
        $this->seeFileFound('c3.php');
        $this->seeFileFound('composer.json');
        $this->seeInThisFile('codeception/c3');
    }
}
