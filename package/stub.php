#!/usr/bin/env php
<?php
Phar::mapPhar();

require_once 'phar://codecept.phar/vendor/autoload.php';

use Symfony\Component\Console\Application,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputDefinition,
    Symfony\Component\Console\Input\InputOption;

require_once 'phar://codecept.phar/vendor/bin/codecept';

__HALT_COMPILER();