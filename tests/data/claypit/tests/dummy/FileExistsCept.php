<?php
require '_bootstrap.php';

$I = new DumbGuy($scenario);
$I->wantTo('check config exists');
$I->seeFileFound($codeception);