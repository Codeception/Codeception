<?php
use Codeception\Util\JsonArray;
use Codeception\Util\Shared\Asserts;

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
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class AngularGuy extends \Codeception\Actor
{
    use Asserts;
    use _generated\AngularGuyActions;

    public function seeInFormResult($expected)
    {
        $jsonArray = new JsonArray($this->grabTextFrom(['id' => 'data']));
        $this->assertTrue($jsonArray->containsArray($expected), var_export($jsonArray->toArray(), true));
    }

    public function submit()
    {
        $this->click('Submit');
    }
}
