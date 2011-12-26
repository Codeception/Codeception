<?php
require_once __DIR__.'/../autoload.php';
$version = \Codeception\Codecept::VERSION;

chdir(__DIR__.'/../');
system('pearfarm build');
@mkdir("package/pear");
@unlink("Codeception-$version.tgz");
system('git clone git@github.com:DavertMik/pear.git package/pear/');
system('pirum add package/pear package.xml');
chdir('package/pear');
system('git add .');
system('git commit -m="version '.$version.'"');
system('git push');