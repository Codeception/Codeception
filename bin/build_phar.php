<?php

if (file_exists(__DIR__.'/../package/codecept.phar')) unlink(__DIR__.'/../package/codecept.phar');

$root = __DIR__.'/../';
require $root.'/autoload.php';

// download composer
chdir(__DIR__.'/../');
@mkdir("package/phar");
chdir('package/phar');
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

$p = new Phar('codecept.phar');
$p->startBuffering();
$p->buildFromDirectory(__DIR__.'/../package/phar/vendor','~\.html.dist$~');
$p->buildFromDirectory(__DIR__.'/../package/phar/vendor','~\.tpl.dist$~');
$p->buildFromDirectory(__DIR__.'/../package/phar/vendor','~\.php$~');
$p->buildFromDirectory(__DIR__.'/../package/phar/vendor','~\.js$~');
$p->addFile(__DIR__.'/../package/bin','codecept');
$p->setStub(file_get_contents(__DIR__.'/../package/stub.php'));
$p->stopBuffering();
$p->compressFiles(Phar::GZ);

copy('codecept.phar', __DIR__.'/../package/codecept.phar');

chdir('..');
ob_start();
@system('del /s /q /F phar');
@system('rd /s /q phar');
@system('rm -rf phar');
ob_clean();

echo "PHAR build succesfull";
