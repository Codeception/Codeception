<?php
require_once __DIR__.'/../autoload.php';
$version = \Codeception\Codecept::VERSION;
$branch = explode('.', $version);
array_pop($branch);
$branch = implode('.',$branch);
system("git tag $version");
system("git push origin $branch --tags");