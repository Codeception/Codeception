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
$browser = $client->getBrowser('http://symfony-project.org');

// Starts the browser
$browser->start();

$browser
    ->open('http://google.com')
    ->waitForPageToLoad(10000)
    ->open('http://symfony.com')
    ->waitForPageToLoad(10000)
;

echo "Page title: ".$browser->getTitle()."\n";

$browser->stop();
