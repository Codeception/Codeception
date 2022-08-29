<?php

declare(strict_types=1);

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
 * @method void pause($vars = [])
 *
 * @SuppressWarnings(PHPMD)
*/
class UnitTester extends \Codeception\Actor
{
    use _generated\UnitTesterActions;

    /**
     * Define custom actions here
     */
}
