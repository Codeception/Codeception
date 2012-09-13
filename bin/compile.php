<?php

$root = __DIR__.'/../';
require $root.'/vendor/autoload.php';
require $root.'/package/Compiler.php';

use Codeception\Compiler;

$compiler = new Compiler();
$compiler->compile($root.'/package/codecept.phar');

