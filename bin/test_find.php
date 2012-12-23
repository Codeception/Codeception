<?php
require_once __DIR__.'/../autoload.php';

$docs = \Symfony\Component\Finder\Finder::create()->files('*.php')->sortByName()->in(getcwd().'/src/Codeception/Util/');

foreach ($docs as $doc) {
    print_r($doc);
}
