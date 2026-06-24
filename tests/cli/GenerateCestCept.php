<?php

$I = new \Tests\Support\Step\GeneratorSteps($scenario);
$I->wantTo('generate sample Cest');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('generate:cest dummy DummyClass');
$I->seeFileWithGeneratedClass('DummyClass');
