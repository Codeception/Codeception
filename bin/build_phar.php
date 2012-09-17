<?php

if (file_exists(__DIR__.'/../package/codecept.phar')) unlink(__DIR__.'/../package/codecept.phar');

$root = __DIR__.'/../';
require $root.'/vendor/autoload.php';
require $root.'/package/Compiler.php';


// download composer
chdir(__DIR__.'/../');
file_put_contents('composer.phar', file_get_contents('http://getcomposer.org/installer'));
system('php composer.phar install');
system('php composer.phar update');

$compiler = new \Codeception\Compiler();
$compiler->compile();

copy('codecept.phar', 'package/codecept.phar');
unlink('codecept.phar');

echo system('php codecept.phar');

echo "PHAR build finished";