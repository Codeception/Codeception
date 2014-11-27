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
     * // If config is: 'http://mysite.com'
     * // or config is: 'http://www.mysite.com'
     * // or config is: 'http://company.mysite.com'
     *
     * $I->amOnSubdomain('user');
     * $I->amOnPage('/');
     * // moves to http://user.mysite.com/
     * ?>
     * ```
     *
     * @param $subdomain
     *
     * @return mixed
     */
    public function amOnSubdomain($subdomain);

    /**
     * Open web page at the given absolute URL and sets its hostname as the base host.
     *
     * ``` php
     * <?php
     * $I->amOnUrl('http://codeception.com');
     * $I->amOnPage('/quickstart'); // moves to http://codeception.com/quickstart
     * ?>
     * ```
     */
    public function amOnUrl($url);

    public function _getUrl();
}
