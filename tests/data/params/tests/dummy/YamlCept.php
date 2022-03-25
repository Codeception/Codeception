<?php

$I = new DummyTester($scenario);

$I->seeVarEquals('KEY3', 1);
$I->seeVarEquals('KEY4', ['foo', 'bar', 'baz']);

