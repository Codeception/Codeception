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
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class UnitTester extends \Codeception\Actor
{
    use _generated\UnitTesterActions;


    /**
     * @When exception is thrown
     */
    public function exceptionIsThrown()
    {
        throw new \RuntimeException('in gherkin');
    }
}
