<?php

namespace Codeception\Lib\Interfaces;

interface Remote
{
    /**
     * Sets 'url' configuration parameter to hosts subdomain.
     * It does not open a page on subdomain. Use `amOnPage` for that
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
     * Open web page at absolute URL.
     * Base url will be reconfigured to use the host of provided Url.
     *
     * ``` php
     * <?php
     * $I->amOnUrl('http://codeception.com');
     * $I->anOnPage('/quickstart'); // moves to http://codeception.com/quickstart
     * ?>
     * ```
     */
    public function amOnUrl($url);

    public function _getUrl();
}
