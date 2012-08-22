<?php
require_once __DIR__.'/../autoload.php';
$version = \Codeception\Codecept::VERSION;

$root = __DIR__.'/../';

chdir($root);
@mkdir("package/pear");
chdir('package/pear');
// Clone GitHub

// install Codeception from Composer
$composer = file_get_contents('http://getcomposer.org/installer');
file_put_contents('composer.phar', $composer);
system('php composer.phar install');
system('php composer.phar install');

// grab pear repository
@mkdir("package/pear-site");
system('git clone git@github.com:Codeception/pear.git package/pear-site');

// build package
system('pearfarm build');
system('pirum add package/pear-site Codeception-'.$version.'.tgz');
@unlink("Codeception-$version.tgz");

// push new package
chdir('package/pear-site');
system('git add -A');
system('git commit -m="version '.$version.'"');
system('git push origin gh-pages');
chdir('..');
sleep(2);

// clean up
chdir($root.'/package');
@system('del /s /q /F pear');
@system('rd /s /q pear');
@system('rm -rf pear');
echo "\n\nPEAR BUILD SUCCESSFUL";