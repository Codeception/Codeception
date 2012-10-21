<?php

if (!is_dir($vendor = __DIR__.'/../vendor')) {
    die('Install dependencies first');
}

require($vendor.'/autoload.php');
require($vendor.'/behat/mink/tests/Behat/Mink/Driver/GeneralDriverTest.php');
require($vendor.'/behat/mink/tests/Behat/Mink/Driver/HeadlessDriverTest.php');
require($vendor.'/behat/mink/tests/Behat/Mink/Driver/JavascriptDriverTest.php');
