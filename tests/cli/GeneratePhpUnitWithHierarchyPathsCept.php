<?php
$I = new CliGuy($scenario);
$I->wantTo('generate sample Test in hierarchy path');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('generate:phpunit dummy Dummy/Foo/Bar');
$I->seeFileFound('Dummy/Foo/BarTest.php');
$I->seeInThisFile('namespace Dummy/Foo;');
$I->seeInThisFile('class Bar extends \PHPUnit_Framework_TestCase');
$I->seeInThisFile('function setUp()');