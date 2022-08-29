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
class MessageGuy extends \Codeception\Actor
{
    use _generated\MessageGuyActions;
}
