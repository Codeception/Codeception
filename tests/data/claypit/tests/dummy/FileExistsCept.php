<?php
$I = new DumbGuy($scenario);
$I->wantTo('check config exists');
$codeception = 'codeception.yml';
$I->seeFileFound($codeception);