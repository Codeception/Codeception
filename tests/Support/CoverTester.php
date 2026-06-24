<?php

declare(strict_types=1);

namespace Tests\Support;

/**
 * Inherited Methods
 * @method void wantTo($text)
 * @method void wantToTest($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause($vars = [])
 *
 * @SuppressWarnings(PHPMD)
*/
class CoverTester extends \Codeception\Actor
{
    use _generated\CoverTesterActions;

    public function seeCoverageStatsNotEmpty()
    {
        $this->seeShellOutputMatches(
            '#info\n\s+Methods:\s+\d+\.\d+% \( [01]/ 1\)\s+Lines:\s+\d+\.\d+% \(  [345]/  [345]\)#s'
        );
    }
}
