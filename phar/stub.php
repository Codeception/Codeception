#!/usr/bin/env php
<?php
Phar::mapPhar('codecept.phar');

require_once 'phar://codecept.phar/vendor/codeception/codeception/app.php';

__HALT_COMPILER();
