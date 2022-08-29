<?php

$autoloadFile = './vendor/codeception/codeception/autoload.php';
if ((!isset($argv) || (isset($argv) && !in_array('--no-redirect', $argv))) && file_exists('./vendor/autoload.php') && file_exists($autoloadFile) && __FILE__ != realpath($autoloadFile)) {
    //for global installation or phar file
    fwrite(
        STDERR,
        "\n==== Redirecting to Composer-installed version in vendor/codeception. You can skip this using --no-redirect ====\n"
    );

    if (file_exists('./vendor/codeception/codeception/app.php')) {
        //codeception v4+
        require './vendor/codeception/codeception/app.php';
    } else {
        //older version
        require $autoloadFile;
        //require package/bin instead of codecept to avoid printing hashbang line
        require './vendor/codeception/codeception/package/bin';
    }

    die;
} elseif (file_exists(__DIR__ . '/vendor/autoload.php')) {
    // for phar
    require_once __DIR__ . '/vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../../autoload.php')) {
    //for composer
    require_once __DIR__ . '/../../autoload.php';
}
unset($autoloadFile);
if (isset($argv)) {
    $argv = array_values(array_diff($argv, ['--no-redirect']));
}
if (isset($_SERVER['argv'])) {
    $_SERVER['argv'] = array_values(array_diff($_SERVER['argv'], ['--no-redirect']));
}
