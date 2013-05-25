<?php
namespace Codeception\Util;

interface RemoteInterface
{
    /**
     * Moves to subdomain of confogured site.
     *
     * ``` php
     * <?php
     * // If config is: 'http://mysite.com'
     * // or config is: 'http://www.mysite.com'
     * // or config is: 'http://company.mysite.com'
     *
     * $I->amOnSubdomain('user');
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
