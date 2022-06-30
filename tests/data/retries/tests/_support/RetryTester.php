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
 *
 * @SuppressWarnings(PHPMD)
*/
class RetryTester extends \Codeception\Actor
{
    use _generated\RetryTesterActions;

    use \Codeception\Lib\Actor\Shared\Retry;
}
