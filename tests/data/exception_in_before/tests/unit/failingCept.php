<?php 
$I = new UnitTester($scenario);
$I->wantTo('see that this test was not executed');
throw new \RuntimeException('in cept');