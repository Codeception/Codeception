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
 *
 * @SuppressWarnings(PHPMD)
*/
class RetryTester extends \Codeception\Actor
{
    use _generated\RetryTesterActions;

    use \Codeception\Lib\Actor\Shared\Retry;

   /**
    * Define custom actions here
    */
}
