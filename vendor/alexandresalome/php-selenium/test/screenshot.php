<?php
/*
 * This file is part of PHP Selenium Library.
 * (c) Alexandre Salomé <alexandre.salome@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/../vendor/autoload.php';

$client  = new Selenium\Client('localhost', 4444);
$browser = $client->getBrowser('http://alexandre-salome.fr');

// Starts the browser
$browser->start();

$data = $browser
    ->open('/')
    ->waitForPageToLoad(10000)
    ->captureEntirePageScreenShotToString("")
;

$browser->stop();

$png = base64_decode(str_replace("\n", "", $data));
file_put_contents('test.png', $png);
echo "Screenshot is in file test.png\n";

