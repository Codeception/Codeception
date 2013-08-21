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

    public function _getResponseCode();

    public function _sendRequest($url);

    /**
     * Checks that cookie is set.
     *
     * @param $cookie
     * @return mixed
     */
    public function seeCookie($cookie);

    /**
     * Checks that cookie doesn't exist
     *
     * @param $cookie
     * @return mixed
     */
    public function dontSeeCookie($cookie);

    /**
     * Sets a cookie.
     *
     * @param $cookie
     * @param $value
     * @return mixed
     */
    public function setCookie($cookie, $value);

    /**
     * Unsets cookie
     *
     * @param $cookie
     * @return mixed
     */
    public function resetCookie($cookie);

    /**
     * Grabs a cookie value.
     *
     * @param $cookie
     * @return mixed
     */
    public function grabCookie($cookie);
}
