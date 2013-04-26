<?php

$root = __DIR__.'/../';
require $root.'/vendor/autoload.php';
require $root.'/package/Compiler.php';
use Codeception\Compiler;

// download composer

//if (!file_exists('composer.phar')) {
//	file_put_contents('composer.phar', file_get_contents('http://getcomposer.org/installer'));
//}
//system('php composer.phar update');

chdir($root);
$compiler = new Compiler();
$compiler->compile();

if (file_exists('codecept.phar.gz')) {
    chmod('codecept.phar.gz', 755);
    copy('codecept.phar.gz', 'package/codecept.phar');
} else {
    copy('codecept.phar', 'package/codecept.phar');
    @exec('chmod 755 package/codecept.phar');
}
@unlink('codecept.phar');
@unlink('codecept.phar.gz');

echo system('php package/codecept.phar');

echo "\nPHAR build finished\n\n";