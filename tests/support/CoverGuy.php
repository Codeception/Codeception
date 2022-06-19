<?php

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method self execute($callable)
 * @method self expectTo($prediction)
 * @method self expect($prediction)
 * @method self amGoingTo($argumentation)
 * @method self am($role)
 * @method self lookForwardTo($achieveValue)
 * @method self comment($description)
 * @method void haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
*/
class CoverGuy extends \Codeception\Actor
{
    use _generated\CoverGuyActions;

    public function seeCoverageStatsNotEmpty()
    {
        $this->seeShellOutputMatches(
            '#info\n\s+Methods:\s+\d+\.\d+% \( [01]/ 1\)\s+Lines:\s+\d+\.\d+% \(  [345]/  [345]\)#s'
        );
    }
}
