<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart

/**
 * C3 - Codeception Code Coverage
 *
 * @author tiger
 */

define('C3_CODECOVERAGE_DEBUG', PHP_SAPI === 'cli');

if (C3_CODECOVERAGE_DEBUG) {
    $_SERVER['REQUEST_URI'] = 'c3/report/html';
    $_SERVER['HTTP_X_CODECEPTION_CODECOVERAGE'] = 'test';
}

if (!array_key_exists('HTTP_X_CODECEPTION_CODECOVERAGE', $_SERVER)) {
    return;
}

// Autoload Codeception classes
if (stream_resolve_include_path(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/codecept.phar')) {
    require_once __DIR__ . '/codecept.phar/autoload.php';
} elseif (stream_resolve_include_path('Codeception/autoload.php')) {
    require_once 'Codeception/autoload.php';
}

if (!class_exists('Codeception')) {
    throw new Exception('Codeception is not loaded. Please check that either PHAR or Composer or PEAR package can be used');
}

// Load Codeception Config
$config_file = realpath(__DIR__).DIRECTORY_SEPARATOR.'codeception.yml';
if (array_key_exists('HTTP_X_CODECEPTION_CODECOVERAGE_CONFIG', $_SERVER)) {
    $config_file = realpath(__DIR__).DIRECTORY_SEPARATOR.$_SERVER['HTTP_X_CODECEPTION_CODECOVERAGE_CONFIG'];
    if (!file_exists($config_file))
        throw new Exception(sprintf("Codeception config file '%s' not found", $config_file));
}
\Codeception\Configuration::config($config_file);

__c3_prepare();

// evaluate base path for c3-related files
$path = realpath(C3_CODECOVERAGE_MEDIATE_STORAGE) . DIRECTORY_SEPARATOR . 'codecoverage';

$requested_c3_report = (strpos($_SERVER['REQUEST_URI'], 'c3/report') !== false);

$current_report = $path;
$complete_report = $path.'.serialized';

if ($requested_c3_report) {

    set_time_limit(0);
    if (file_exists($current_report)) {
        if (file_exists($complete_report)) unlink($complete_report);

        rename($current_report, $complete_report);
    }

    $codeCoverage = __c3_factory($complete_report);

    switch (ltrim(strrchr($_SERVER['REQUEST_URI'], '/'), '/')) {
        case 'html':
            __c3_send_file(__c3_build_html_report($codeCoverage, $path));
            break;

        case 'clover':
            __c3_send_file(__c3_build_clover_report($codeCoverage, $path));
            break;

        case 'serialized':
            __c3_send_file($complete_report);
            break;
    }

} else {
    if (file_exists($complete_report)) unlink($complete_report);

    $codeCoverage = __c3_factory($current_report);
    $codeCoverage->start(C3_CODECOVERAGE_TESTNAME);

    register_shutdown_function(function () use ($codeCoverage, $current_report) {
        $codeCoverage->stop();
        file_put_contents($current_report, serialize($codeCoverage));
    });
}

function __c3_build_html_report(PHP_CodeCoverage $codeCoverage, $path)
{
    $writer = new PHP_CodeCoverage_Report_HTML();
    $writer->process($codeCoverage, $path . 'html');

    if (file_exists($path . '.tar')) {
        unlink($path . '.tar');
    }

    $phar = new PharData($path . '.tar');
    $phar->setSignatureAlgorithm(Phar::SHA1);
    $files = $phar->buildFromDirectory($path . 'html');
    array_map('unlink', $files);

    if (in_array('GZ', Phar::getSupportedCompression())) {
        if (file_exists($path . '.tar.gz')) {
            unlink($path . '.tar.gz');
        }

        $phar->compress(\Phar::GZ);

        // close the file so that we can rename it
        unset($phar);

        unlink($path . '.tar');
        rename($path . '.tar.gz', $path . '.tar');
    }

    return $path . '.tar';
}

function __c3_build_clover_report(PHP_CodeCoverage $codeCoverage, $path)
{
    $writer = new PHP_CodeCoverage_Report_Clover();
    $writer->process($codeCoverage, $path . '.clover.xml');

    return $path . '.clover.xml';
}

function __c3_send_file($filename)
{
    if (!headers_sent()) {
        readfile($filename);
    }

    exit;
}

function __c3_prepare()
{
    // workaround for 'zend_mm_heap corrupted' problem
    gc_disable();

    if ((integer)ini_get('memory_limit') < 384) {
        ini_set('memory_limit', '384M');
    }

    defined('C3_CODECOVERAGE_MEDIATE_STORAGE')
        || define('C3_CODECOVERAGE_MEDIATE_STORAGE', __DIR__ . '/c3tmp');

    defined('C3_CODECOVERAGE_PROJECT_ROOT')
        || define('C3_CODECOVERAGE_PROJECT_ROOT', __DIR__);

    define('C3_CODECOVERAGE_TESTNAME', $_SERVER['HTTP_X_CODECEPTION_CODECOVERAGE']);
    if (!is_dir(C3_CODECOVERAGE_MEDIATE_STORAGE)) {
        mkdir(C3_CODECOVERAGE_MEDIATE_STORAGE, 0777, true);
    }
}

/**
 * @param $filename
 * @return null|PHP_CodeCoverage
 */
function __c3_factory($filename)
{
    $phpCoverage = is_readable($filename)
        ? unserialize(file_get_contents($filename))
        : null;

    $c3 = new \Codeception\CodeCoverage($phpCoverage);
    return $c3->getPhpCodeCoverage();
}


// @codeCoverageIgnoreEnd
