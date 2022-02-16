<?php

namespace Codeception\Lib\Interfaces;

use Symfony\Component\DomCrawler\Crawler;

interface ElementLocator
{
    /**
     * Locates element using available Codeception locator types:
     *
     * * XPath
     * * CSS
     * * Strict Locator
     *
     * Use it in Helpers or GroupObject or Extension classes:
     *
     * ```php
     * <?php
     * $els = $this->getModule('{{MODULE_NAME}}')->_findElements('.items');
     * $els = $this->getModule('{{MODULE_NAME}}')->_findElements(['name' => 'username']);
     *
     * $editLinks = $this->getModule('{{MODULE_NAME}}')->_findElements(['link' => 'Edit']);
     * // now you can iterate over $editLinks and check that all them have valid hrefs
     * ```
     *
     * WebDriver module returns `Facebook\WebDriver\Remote\RemoteWebElement` instances
     * PhpBrowser and Framework modules return `Symfony\Component\DomCrawler\Crawler` instances
     *
     * @return Crawler|array of interactive elements
     * @api
     */
    public function _findElements(mixed $locator): iterable;
}
