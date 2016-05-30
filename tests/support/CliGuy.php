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
class CliGuy extends \Codeception\Actor
{
    use _generated\CliGuyActions;

    /**
     * @param $file
     */
    public function seeInSupportDir($file)
    {
        $this->seeFileFound($file, 'tests/support');
    }
}
