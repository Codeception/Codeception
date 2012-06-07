<?php
Phar::mapPhar();

chdir(__DIR__);

require_once 'phar://codecept.phar/autoload.php';

use Symfony\Component\Console\Application,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputDefinition,
    Symfony\Component\Console\Input\InputOption;

require_once 'phar://codecept.phar/codecept';

__HALT_COMPILER();