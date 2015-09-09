<?php
// @env email
$I = new MessageGuy($scenario);
$I->wantTo('Test emails');
$I->expect('emails are sent');