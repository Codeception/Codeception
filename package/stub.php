#!/usr/bin/env php
<?php
Phar::mapPhar();

chdir(__DIR__);

require_once 'phar://codecept.phar/autoload.php';

require_once 'phar://codecept.phar/codecept';

__HALT_COMPILER();