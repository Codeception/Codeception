<?php
/*
 * This file is part of PHP Selenium Library.
 * (c) Alexandre Salomé <alexandre.salome@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This script is used to generate the browser class using the Selenium
 * iedoc.xml file
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */

use Selenium\Specification\Loader\XmlLoader;
use Selenium\Specification\Dumper\BrowserDumper;
use Selenium\Specification\Specification;

require_once __DIR__.'/../vendor/autoload.php';

// Check parameter
if (!isset($argv[1]) || !file_exists($argv[1])) {
    echo "Command usage: ./generate_browser.php <iedoc>\n";
    echo "\n";
    echo " <iedoc> is the XML file provided by Selenium containing all methods\n";
    if (isset($argv[1])) {
        echo "\n";
        echo "ERROR: The file provided does not exists !\n";
    }
    exit;
}

$inputFile  = $argv[1];
$outputFile = __DIR__.'/../src/Selenium/Browser.php';

$specification = new Specification();
$loader = new XmlLoader($specification);
$loader->load($inputFile);
$dumper = new BrowserDumper($specification);

file_put_contents($outputFile, $dumper->dump());
