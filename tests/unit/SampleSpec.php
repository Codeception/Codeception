<?php
$I = new CodeGuy($scenario);
$I->wantTo('test a code specification');
$I->declareStubs(function () {


});
$I->haveClassInstance('\Codeception\Step\Action', array());
$I->execute('getName');
$I->seeResult('Action');