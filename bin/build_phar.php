<?php

$root = __DIR__.'/../';
require $root.'/vendor/autoload.php';
require $root.'/package/Compiler.php';
use Codeception\Compiler;

// download composer

if (!file_exists('composer.phar')) {
	file_put_contents('composer.phar', file_get_contents('http://getcomposer.org/installer'));
}
system('php composer.phar update');

$compiler = new Compiler();
$compiler->compile();


chmod('codecept.phar', 755);
copy('codecept.phar', 'package/codecept.phar');

unlink('codecept.phar');
unlink('codecept.phar.gz');

echo system('php package/codecept.phar');

echo "PHAR build finished";