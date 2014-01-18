<?php
namespace Codeception\Util;

use Codeception\Exception\ContentNotFound;
use Codeception\Exception\ElementNotFound;
use Codeception\PHPUnit\Constraint\CrawlerNot;
use Symfony\Component\CssSelector\CssSelector;
use Symfony\Component\CssSelector\Exception\ParseException;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Abstract module for PHP frameworks connected via Symfony BrowserKit components
 * Each framework is connected with it's own connector defined in \Codeception\Util\Connector
 * Each module for framework should extend this class.
 *
 */

abstract class Framework extends InnerBrowser
{

}
