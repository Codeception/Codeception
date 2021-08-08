<?php

namespace Codeception\Lib\Interfaces;

interface Remote
{
    /**
     * Changes the subdomain for the 'url' configuration parameter.
     * Does not open a page; use `amOnPage` for that.
     *
     * ``` php
     * <?php
     * // If config is: 'https://mysite.com'
     * // or config is: 'https://www.mysite.com'
     * // or config is: 'https://company.mysite.com'
     *
     * $I->amOnSubdomain('user');
     * $I->amOnPage('/');
     * // moves to https://user.mysite.com/
     * ```
     *
     */
    public function amOnSubdomain(string $subdomain): void;

    /**
     * Open web page at the given absolute URL and sets its hostname as the base host.
     *
     * ``` php
     * <?php
     * $I->amOnUrl('https://codeception.com');
     * $I->amOnPage('/quickstart'); // moves to https://codeception.com/quickstart
     * ```
     */
    public function amOnUrl(string $url): void;

    public function _getUrl();
}
