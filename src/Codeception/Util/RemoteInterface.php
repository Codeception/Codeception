<?php
namespace Codeception\Util;

interface RemoteInterface
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
     * @param $subdomain
     * @return mixed
     */
    public function amOnSubdomain($subdomain);

    public function _getUrl();

    public function _setCookie($cookie, $value);

    public function _setHeader($header, $value);

    public function _getResponseHeader($header);

    public function _getResponseCode();

    public function _sendRequest($url);
}
