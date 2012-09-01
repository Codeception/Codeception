<?php

$root = __DIR__.'/../';
require $root.'/vendor/autoload.php';

use Codeception\Compiler;

$compiler = new Compiler();
$compiler->compile();

