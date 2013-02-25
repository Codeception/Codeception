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

@unlink('package/codecept.phar');

if (file_exists('codecept.phar.gz')) {
    chmod('codecept.phar.gz', 755);
    copy('codecept.phar.gz', 'package/codecept.phar');
} else {
    chmod('codecept.phar', 755);
    copy('codecept.phar', 'package/codecept.phar');
}
@unlink('codecept.phar');
@unlink('codecept.phar.gz');

chmod('package/codecept.phar', 755);

echo system('php package/codecept.phar');

echo "PHAR build finished";