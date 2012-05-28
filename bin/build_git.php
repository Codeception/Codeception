<?php
require_once __DIR__.'/../autoload.php';
$version = \Codeception\Codecept::VERSION;
system("git tag $version");
system("git push remote master --tags");