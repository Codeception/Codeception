<?php
$I = new CliGuy($scenario);
$I->wa1ntTo('generate sample Test');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('generate:phpunit dummy Dummy');
$I->seeFileFound('DummyTest.php');
$I->seeInThisFile('class DummyTest extends \PHPUnit_Framework_TestCase');
$I->seeInThisFile('function setUp()');