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
class CoverGuy extends \Codeception\Actor
{
    use _generated\CoverGuyActions;

   /**
    * Define custom actions here
    */
    public function seeCoverageStatsNotEmpty()
    {
        $this->seeShellOutputMatches(
            '#info\n\s+Methods:\s+\d+\.\d+% \( [01]/ 1\)\s+Lines:\s+\d+\.\d+% \(  [345]/  [345]\)#s'
        );
    }
}
