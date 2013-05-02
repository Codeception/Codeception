<?php
require_once __DIR__.'/../autoload.php';
$version = \Codeception\Codecept::VERSION;

$root = __DIR__.'/../';

chdir($root);

// install Codeception from Composer
// download composer
file_put_contents('composer.phar', file_get_contents('http://getcomposer.org/installer'));
system('php composer.phar install');
system('php composer.phar update');

// grab pear repository
system('git clone git@github.com:Codeception/pear.git package/pear');

// build package
system('pearfarm build');
system('pirum add package/pear Codeception-'.$version.'.tgz');
@unlink("Codeception-$version.tgz");

// push new package
chdir('package/pear');
system('git add -A');
system('git commit -m="version '.$version.'"');
system('git push origin gh-pages');
chdir('..');
sleep(2);

// clean up
chdir($root.'/package');
ob_start();
@system('del /s /q /F package/pear');
@system('rd /s /q pear');
@system('rm -rf pear');
ob_clean();
echo "\n\nPEAR BUILD SUCCESSFUL";