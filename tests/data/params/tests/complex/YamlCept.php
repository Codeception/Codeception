<?php

$I = new DummyTester($scenario);

$I->seeVarEquals(0, 1);
$I->seeVarEquals(1, ['foo', 'bar', 'baz']);
$I->seeVarEquals(2, ['foo', 'bar', 'baz']);
$I->seeVarEquals(3, 'bar');
