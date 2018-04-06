<?php

/**
 * This file allows for tests to be skipped.
 * For now conditions are simple.
 * We check if changes in the source with respect to the configured branch are limited to framework files,
 * if that is the case and the current framework isn't one with changed files then we skip it.
 */
$branch ="2.4";


function stderr($message)
{
    fwrite(STDERR, $message . "\n");
}

$currentFramework = getenv('FRAMEWORK');

if ($currentFramework === 'Codeception') {
    stderr('Codeception tests are always executed');
    die();
}
$files = [];
exec("git diff --name-only $branch", $files);

// Regexes for frameworks:
$regexes = [
    'Yii2' => '/.*Yii2.*/',
    'Lumen' => '/.*(Lumen|LaravelCommon).*/',
    'Laravel' => '/.*Laravel.*/',
    'Phalcon' => '/.*Phalcon.*/',
    'Symfony' => '/.*Symfony.*/',
    'Yii1' => '/.*Yii1.*/',
    'ZendExpressive' => '/.*ZendExpressive.*/',
    'Zend1' => '/.*ZF1.*/',
    'Zend2' => '/.*ZF2.*/',
];

// First check if changes include files that are not framework files.
$frameworkOnly = true;
$frameworks = [];
foreach ($files as $file) {
    $match = false;
    foreach ($regexes as $framework => $regex) {
        if (preg_match($regex, $file)) {
            $match = true;
            $frameworks[$framework] = $framework;
            break;
        }
    }
    if (!$match) {
        $frameworkOnly = false;
        break;
    }
}

if ($frameworkOnly) {
    stderr('Changes limited to frameworks: ' . implode(', ', $frameworks));
    if (!isset($frameworks[$currentFramework])) {
        stderr("Skipping test for framework: $currentFramework");
        echo "export FRAMEWORK=\n";
        echo "export PECL=\n";
        echo "export FXP=\n";
        echo "export CI_USER_TOKEN=\n";
    }
}
