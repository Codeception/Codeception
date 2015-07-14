<?php
// for phar
if (file_exists(__DIR__.'/vendor/autoload.php')) {
    require_once(__DIR__.'/vendor/autoload.php');
} elseif (file_exists(__DIR__.'/../../autoload.php')) {
    require_once __DIR__ . '/../../autoload.php';
}

// loading WebDriver aliases
if (!class_exists('RemoteWebDriver') and class_exists('Facebook\WebDriver\Remote\RemoteWebDriver')) {
    class RemoteWebDriver extends \Facebook\WebDriver\Remote\RemoteWebDriver {};
    class InvalidSelectorException extends Facebook\WebDriver\Exception\InvalidSelectorException {};
    class NoSuchElementException extends Facebook\WebDriver\Exception\NoSuchElementException {};
    class WebDriverCurlException extends Facebook\WebDriver\Exception\WebDriverCurlException {};
    class WebDriverActions extends Facebook\WebDriver\Interactions\WebDriverActions {};
    class LocalFileDetector extends Facebook\WebDriver\Remote\LocalFileDetector {};
    class WebDriverCapabilityType extends Facebook\WebDriver\Remote\WebDriverCapabilityType {};
    class WebDriverAlert extends Facebook\WebDriver\WebDriverAlert {};
    class WebDriverBy extends Facebook\WebDriver\WebDriverBy {};
    class WebDriverDimension extends Facebook\WebDriver\WebDriverDimension {};
    class RemoteWebElement extends Facebook\WebDriver\Remote\RemoteWebElement {};
    class WebDriverExpectedCondition extends Facebook\WebDriver\WebDriverExpectedCondition {};
    class WebDriverKeys extends Facebook\WebDriver\WebDriverKeys {};
    class WebDriverSelect extends Facebook\WebDriver\WebDriverSelect {};
    class WebDriverTimeouts extends Facebook\WebDriver\WebDriverTimeouts {};
    class WebDriverWindow extends Facebook\WebDriver\WebDriverWindow {};
    interface WebDriverElement extends Facebook\WebDriver\WebDriverElement {};
}

// function not autoloaded in PHP, thus its a good place for them
function codecept_debug($data)
{
    \Codeception\Util\Debug::debug($data);
}

function codecept_root_dir($appendPath = '')
{
    return \Codeception\Configuration::projectDir() . $appendPath;
}

function codecept_output_dir($appendPath = '')
{
    return \Codeception\Configuration::outputDir() . $appendPath;
}

function codecept_log_dir($appendPath = '')
{
    return \Codeception\Configuration::outputDir() . $appendPath;
}

function codecept_data_dir($appendPath = '')
{
    return \Codeception\Configuration::dataDir() . $appendPath;
}

function codecept_relative_path($path)
{
    return substr($path, strlen(codecept_root_dir()));
}