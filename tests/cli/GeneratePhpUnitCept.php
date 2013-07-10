<?php
$I = new CliGuy\GeneratorSteps($scenario);
$I->wantTo('generate sample Test');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('generate:phpunit dummy Dummy');
$I->seeFileWithGeneratedClass('DummyTest');
$I->seeInThisFile('class DummyTest extends \PHPUnit_Framework_TestCase');
$I->seeInThisFile('function setUp()');