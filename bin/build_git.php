<?php
require_once __DIR__.'/../autoload.php';
$version = \Codeception\Codecept::VERSION;
$branch = implode('.',array_pop(explode('.', $version)));
system("git tag $version");
system("git push origin $branch --tags");