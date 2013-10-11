gu<?php
require_once __DIR__.'/../autoload.php';
$version = \Codeception\Codecept::VERSION;

chdir(__DIR__.'/../');

$docs = \Symfony\Component\Finder\Finder::create()->files('*.md')->sortByName()->in(__DIR__.'/../docs');

@mkdir("package/site");
system('git clone git@github.com:Codeception/codeception.github.com.git package/site/');

copy('package/codecept.phar','package/site/codecept.phar');
@mkdir("package/site/releases/".\Codeception\Codecept::VERSION,777, true);
copy('package/codecept.phar','package/site/releases/'.\Codeception\Codecept::VERSION.'"/codecept.phar');

chdir('package/site');
system('git add codecept.phar');
system('git commit -m="auto-updated phar archive"');
system('git push');
chdir('..');
sleep(2);
@system('del /s /q /F site');
@system('rd /s /q site');
@system('rm -rf site');
echo "\n\nPACKAGE RELEASE SUCCESSFUL";
