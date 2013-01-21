<?php
$I = new CliGuy($scenario);
$I->wantTo('generate sample Cest');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('generate:test dummy Dummy');
$I->seeFileFound('DummyTest.php');
$I->seeInThisFile('class DummyTest extends \Codeception\TestCase\Test');
$I->seeInThisFile('protected $dumbGuy');