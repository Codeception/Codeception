<?php

if (file_exists(__DIR__.'/../package/codecept.phar')) unlink(__DIR__.'/../package/codecept.phar');

$root = __DIR__.'/../';
require $root.'/vendor/autoload.php';
require $root.'/package/Compiler.php';


// download composer
chdir(__DIR__.'/../');
@mkdir("package/phar");
chdir('package/phar');
@unlink('codecep.phar');
file_put_contents('composer.phar', file_get_contents('http://getcomposer.org/installer'));
file_put_contents('composer.json','
{
    "require": {
        "codeception/codeception":  "*"
    },
    "minimum-stability": "dev"
}
');

system('php composer.phar install');
system('php composer.phar install');


$compiler = new \Codeception\Compiler($root.'/pacakge/phar');
$compiler->compile($root.'/package/codecept.phar');

chdir('..');
ob_start();
@system('del /s /q /F phar');
@system('rd /s /q phar');
@system('rm -rf phar');
ob_clean();

echo system('php codecept.phar');

echo "PHAR build finished";