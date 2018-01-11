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
        $this->seeInShellOutput(
            <<<EOF
info
  Methods: 100.00% ( 1/ 1)   Lines: 100.00% (  4/  4)
EOF
        );
    }
}
