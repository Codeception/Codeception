<?php

if (file_exists(__DIR__.'/../package/codecept.phar')) unlink(__DIR__.'/../package/codecept.phar');

// download composer
$composer = file_get_contents('http://getcomposer.org/installer');
chdir(__DIR__.'/../');
@mkdir("package/phar");
chdir('package/phar');
file_put_contents('composer.phar', $composer);
file_put_contents('composer.json','
{
    "require": {
        "codeception/codeception":  "*",
        "behat/mink-goutte-driver": "*",
        "behat/mink-selenium-driver": "*",
        "behat/mink-selenium2-driver": "*",
        "behat/mink-zombie-driver": "*"
    }
}
');

system('php composer.phar install');
system('php composer.phar install');

$p = new Phar('codecept.phar');
$p->startBuffering();
$p->buildFromDirectory(__DIR__.'/../package/phar/vendor','~\.php$~');
$p->addFile(__DIR__.'/../codecept','codecept');
$p->setStub(file_get_contents(__DIR__.'/../package/stub.php'));
$p->stopBuffering();
$p->compressFiles(Phar::GZ);

echo "copying archive";

copy('codecept.phar', __DIR__.'/../package/codecept');
copy('codecept.phar', __DIR__.'/../package/codecept.phar');

chdir('..');
@system('del /s /q /F phar');
@system('rd /s /q phar');
@system('rm -rf phar');

echo "PHAR build succesfull";
