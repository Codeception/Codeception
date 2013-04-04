<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart

/**
 * C3 - Codeception Code Coverage
 *
 * @author tiger
 */

if (!array_key_exists('HTTP_X_CODECEPTION_CODECOVERAGE', $_SERVER)) {
    return;
}

if (!defined('C3_CODECOVERAGE_MEDIATE_STORAGE')) {

    // workaround for 'zend_mm_heap corrupted' problem
    gc_disable();

    if ((integer)ini_get('memory_limit') < 384) {
        ini_set('memory_limit', '384M');
    }

    define('C3_CODECOVERAGE_MEDIATE_STORAGE', __DIR__ . '/c3tmp');
    define('C3_CODECOVERAGE_PROJECT_ROOT', __DIR__);
    define('C3_CODECOVERAGE_TESTNAME', $_SERVER['HTTP_X_CODECEPTION_CODECOVERAGE']);

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

        return __c3_exit();
    }

    /**
     * @param $filename
     * @return null|PHP_CodeCoverage
     */
    function __c3_factory($filename)
    {
        $phpCoverage = is_readable($filename)
            ? unserialize(file_get_contents($filename))
            : new PHP_CodeCoverage();

        if (isset($_SERVER['HTTP_X_CODECEPTION_CODECOVERAGE_SUITE'])) {
            $suite = $_SERVER['HTTP_X_CODECEPTION_CODECOVERAGE_SUITE'];
            try {
                $settings = \Codeception\Configuration::suiteSettings($suite, \Codeception\Configuration::config());
            } catch (Exception $e) {
                __c3_error($e->getMessage());
            }
        } else {
            $settings = \Codeception\Configuration::config();
        }

        try {
            \Codeception\CodeCoverageSettings::setup($phpCoverage)
                ->filterWhiteList($settings)
                ->filterBlackList($settings);
        } catch (Exception $e) {
            __c3_error($e->getMessage());
        }

        return $phpCoverage;
    }

    function __c3_exit()
    {
        if (!isset($_SERVER['HTTP_X_CODECEPTION_CODECOVERAGE_DEBUG'])) {
            exit;
        }
        return null;
    }

    function __c3_error($message)
    {
        file_put_contents(C3_CODECOVERAGE_MEDIATE_STORAGE . DIRECTORY_SEPARATOR . time() . '-error.txt', $message);
        if (!headers_sent()) {
            header('X-Codeception-CodeCoverage-Error', str_replace("\n", ' ', $message));
        }
        __c3_exit();
    }

    function __c3_clear()
    {
        \Codeception\Util\FileSystem::doEmptyDir(C3_CODECOVERAGE_MEDIATE_STORAGE);
    }
}

if (!is_dir(C3_CODECOVERAGE_MEDIATE_STORAGE)) {
    mkdir(C3_CODECOVERAGE_MEDIATE_STORAGE, 0777, true);
}

// Autoload Codeception classes
if (!class_exists('\\Codeception\\Codecept')) {
    if (stream_resolve_include_path(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
    } elseif (file_exists(__DIR__ . '/codecept.phar')) {
        require_once __DIR__ . '/codecept.phar/autoload.php';
    } elseif (stream_resolve_include_path('Codeception/autoload.php')) {
        require_once 'Codeception/autoload.php';
    } else {
        __c3_error('Codeception is not loaded. Please check that either PHAR or Composer or PEAR package can be used');
    }
}

// Load Codeception Config
$config_file = realpath(__DIR__) . DIRECTORY_SEPARATOR . 'codeception.yml';
if (array_key_exists('HTTP_X_CODECEPTION_CODECOVERAGE_CONFIG', $_SERVER)) {
    $config_file = realpath(__DIR__) . DIRECTORY_SEPARATOR . $_SERVER['HTTP_X_CODECEPTION_CODECOVERAGE_CONFIG'];
}
if (!file_exists($config_file)) {
    __c3_error(sprintf("Codeception config file '%s' not found", $config_file));
}

try {
    \Codeception\Configuration::config($config_file);
} catch (\Exception $e) {
    __c3_error($e->getMessage());
}


// evaluate base path for c3-related files
$path = realpath(C3_CODECOVERAGE_MEDIATE_STORAGE) . DIRECTORY_SEPARATOR . 'codecoverage';

$requested_c3_report = (strpos($_SERVER['REQUEST_URI'], 'c3/report') !== false);

$current_report = $path;
$complete_report = $path . '.serialized';

if ($requested_c3_report) {

    set_time_limit(0);
    if (file_exists($current_report)) {
        if (file_exists($complete_report)) {
            unlink($complete_report);
        }

        rename($current_report, $complete_report);
    }

    $route = ltrim(strrchr($_SERVER['REQUEST_URI'], '/'), '/');

    if ($route == 'clear') {
        __c3_clear();
        return __c3_exit();
    }

    $codeCoverage = __c3_factory($complete_report);

    switch ($route) {
        case 'html':
            __c3_send_file(__c3_build_html_report($codeCoverage, $path));
            return __c3_exit();
        case 'clover':
            __c3_send_file(__c3_build_clover_report($codeCoverage, $path));
            return __c3_exit();
        case 'serialized':
            __c3_send_file($complete_report);
            return __c3_exit();
    }

} else {
    if (file_exists($complete_report)) {
        unlink($complete_report);
    }

    $codeCoverage = __c3_factory($current_report);
    $codeCoverage->start(C3_CODECOVERAGE_TESTNAME);

    register_shutdown_function(
        function () use ($codeCoverage, $current_report) {
            $codeCoverage->stop();
            file_put_contents($current_report, serialize($codeCoverage));
        }
    );
}

// @codeCoverageIgnoreEnd