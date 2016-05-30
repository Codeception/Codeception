<?php
$I = new CliGuy\GeneratorSteps($scenario);
$I->wantTo('generate sample Test');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('generate:test dummy Sommy');
$I->seeFileWithGeneratedClass('SommyTest');
$I->seeInThisFile('class SommyTest extends \Codeception\Test\Unit');
$I->seeInThisFile('protected $guy');
$I->seeInThisFile("function _before(");
