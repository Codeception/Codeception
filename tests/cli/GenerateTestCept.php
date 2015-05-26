<?php
$I = new CliGuy\GeneratorSteps($scenario);
$I->wantTo('generate sample Test');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('generate:test dummy Sommy');
$I->seeFileWithGeneratedClass('SommyTest');
$I->seeInThisFile('class SommyTest extends \Codeception\TestCase\Test');
$I->seeInThisFile('protected $guy');
$I->seeInThisFile("function _before(");