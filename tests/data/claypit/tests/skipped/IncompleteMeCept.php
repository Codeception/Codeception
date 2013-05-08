<?php
$scenario->group('notorun');
$scenario->incomplete();
$I = new SkipGuy($scenario);
$I->wantTo('make it incomplete');
