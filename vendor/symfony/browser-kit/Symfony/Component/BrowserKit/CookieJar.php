<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\BrowserKit;

/**
 * CookieJar.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
class CookieJar
{
    protected $cookieJar = array();

    /**
     * Sets a cookie.
     *
     * @param Cookie $cookie A Cookie instance
     *
     * @api
     */
    public function set(Cookie $cookie)
    {
        $this->cookieJar[$cookie->getDomain()][$cookie->getPath()][$cookie->getName()] = $cookie;
    }

    /**
     * Gets a cookie by name.
     *
     * @param string $name   The cookie name
     * @param string $path   The cookie path
     * @param string $domain The cookie domain
     *
     * @return Cookie|null A Cookie instance or null if the cookie does not exist
     *
     * @api
     */
    public function get($name, $path = '/', $domain = null)
    {
        $this->flushExpiredCookies();

        return isset($this->cookieJar[$domain][$path][$name]) ? $this->cookieJar[$domain][$path][$name] : null;
    }

    /**
     * Removes a cookie by name.
     *
     * @param string $name   The cookie name
     * @param string $path   The cookie path
     * @param string $domain The cookie domain
     *
     * @api
     */
    public function expire($name, $path = '/', $domain = null)
    {
        if (null === $path) {
            $path = '/';
        }

        unset($this->cookieJar[$domain][$path][$name]);

        if (empty($this->cookieJar[$domain][$path])) {
            unset($this->cookieJar[$domain][$path]);

            if (empty($this->cookieJar[$domain])) {
                unset($this->cookieJar[$domain]);
            }
        }
    }

    /**
     * Removes all the cookies from the jar.
     *
     * @api
     */
    public function clear()
    {
        $this->cookieJar = array();
    }

    /**
     * Updates the cookie jar from a response Set-Cookie headers.
     *
     * @param array  $setCookies Set-Cookie headers from an HTTP response
     * @param string $uri        The base URL
     */
    public function updateFromSetCookie(array $setCookies, $uri = null)
    {
        $cookies = array();

        foreach ($setCookies as $cookie) {
            foreach (explode(',', $cookie) as $i => $part) {
                if (0 === $i || preg_match('/^(?P<token>\s*[0-9A-Za-z!#\$%\&\'\*\+\-\.^_`\|~]+)=/', $part)) {
                    $cookies[] = ltrim($part);
                } else {
                    $cookies[count($cookies) - 1] .= ','.$part;
                }
            }
        }

        foreach ($cookies as $cookie) {
            $this->set(Cookie::fromString($cookie, $uri));
        }
    }

    /**
     * Updates the cookie jar from a Response object.
     *
     * @param Response $response A Response object
     * @param string   $uri      The base URL
     */
    public function updateFromResponse(Response $response, $uri = null)
    {
        $this->updateFromSetCookie($response->getHeader('Set-Cookie', false), $uri);
    }

    /**
     * Returns not yet expired cookies.
     *
     * @return array An array of cookies
     */
    public function all()
    {
        $this->flushExpiredCookies();

        $flattenedCookies = array();
        foreach ($this->cookieJar as $path) {
            foreach ($path as $cookies) {
                foreach ($cookies as $cookie) {
                    $flattenedCookies[] = $cookie;
                }
            }
        }

        return $flattenedCookies;
    }

    /**
     * Returns not yet expired cookie values for the given URI.
     *
     * @param string  $uri             A URI
     * @param Boolean $returnsRawValue Returns raw value or urldecoded value
     *
     * @return array An array of cookie values
     */
    public function allValues($uri, $returnsRawValue = false)
    {
        $this->flushExpiredCookies();

        $parts = array_replace(array('path' => '/'), parse_url($uri));
        $cookies = array();
        foreach ($this->cookieJar as $domain => $pathCookies) {
            if ($domain) {
                $domain = ltrim($domain, '.');
                if ($domain != substr($parts['host'], -strlen($domain))) {
                    continue;
                }
            }

            foreach ($pathCookies as $path => $namedCookies) {
                if ($path != substr($parts['path'], 0, strlen($path))) {
                    continue;
                }

                foreach ($namedCookies as $cookie) {
                    if ($cookie->isSecure() && 'https' != $parts['scheme']) {
                        continue;
                    }

                    $cookies[$cookie->getName()] = $returnsRawValue ? $cookie->getRawValue() : $cookie->getValue();
                }
            }
        }

        return $cookies;
    }

    /**
     * Returns not yet expired raw cookie values for the given URI.
     *
     * @param string $uri A URI
     *
     * @return array An array of cookie values
     */
    public function allRawValues($uri)
    {
        return $this->allValues($uri, true);
    }

    /**
     * Removes all expired cookies.
     */
    public function flushExpiredCookies()
    {
        foreach ($this->cookieJar as $domain => $pathCookies) {
            foreach ($pathCookies as $path => $namedCookies) {
                foreach ($namedCookies as $name => $cookie) {
                    if ($cookie->isExpired()) {
                        unset($this->cookieJar[$domain][$path][$name]);
                    }
                }
            }
        }
    }
}
