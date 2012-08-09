<?php

namespace Guzzle\Http\CookieJar;

use Guzzle\Http\Cookie;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;
use Guzzle\Parser\ParserRegistry;

/**
 * Cookie cookieJar that stores cookies an an array
 */
class ArrayCookieJar implements CookieJarInterface, \Serializable
{
    /**
     * @var array Loaded cookie data
     */
    protected $cookies = array();

    /**
     * {@inheritdoc}
     */
    public function remove($domain = null, $path = null, $name = null)
    {
        $cookies = $this->all($domain, $path, $name, false, false);
        $this->cookies = array_filter($this->cookies, function (Cookie $cookie) use ($cookies) {
            return !in_array($cookie, $cookies, true);
        });

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeTemporary()
    {
        $this->cookies = array_filter($this->cookies, function (Cookie $cookie) {
            return !$cookie->getDiscard() && $cookie->getExpires();
        });

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeExpired()
    {
        $currentTime = time();
        $this->cookies = array_filter($this->cookies, function (Cookie $cookie) use ($currentTime) {
            return !$cookie->getExpires() || $currentTime < $cookie->getExpires();
        });

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function all($domain = null, $path = null, $name = null, $skipDiscardable = false, $skipExpired = true)
    {
        return array_values(array_filter($this->cookies, function (Cookie $cookie) use (
            $domain,
            $path,
            $name,
            $skipDiscardable,
            $skipExpired
        ) {
            return false === (($name && $cookie->getName() != $name) ||
                ($skipExpired && $cookie->isExpired()) ||
                ($skipDiscardable && ($cookie->getDiscard() || !$cookie->getExpires())) ||
                ($path && !$cookie->matchesPath($path)) ||
                ($domain && !$cookie->matchesDomain($domain)));
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function add(Cookie $cookie)
    {
        if (!$cookie->getValue() || !$cookie->getName() || !$cookie->getDomain()) {
            return false;
        }

        // Resolve conflicts with previously set cookies
        foreach ($this->cookies as $i => $c) {

            // Check the regular comparison fields
            if ($c->getPath() != $cookie->getPath() || $c->getMaxAge() != $cookie->getMaxAge() ||
                $c->getDomain() != $cookie->getDomain() || $c->getHttpOnly() != $cookie->getHttpOnly() ||
                $c->getPorts() != $cookie->getPorts() || $c->getSecure() != $cookie->getSecure() ||
                $c->getName() != $cookie->getName()
            ) {
                continue;
            }

            // The previously set cookie is a discard cookie and this one is not
            // so allow the new cookie to be set
            if (!$cookie->getDiscard() && $c->getDiscard()) {
                unset($this->cookies[$i]);
                continue;
            }

            // If the new cookie's expiration is further into the future, then
            // replace the old cookie
            if ($cookie->getExpires() > $c->getExpires()) {
                unset($this->cookies[$i]);
                continue;
            }

            // If the value has changed, we better change it
            if ($cookie->getValue() !== $c->getValue()) {
                unset($this->cookies[$i]);
                continue;
            }

            // The cookie exists, so no need to continue
            return false;
        }

        $this->cookies[] = $cookie;

        return true;
    }

    /**
     * Serializes the cookie cookieJar
     *
     * @return string
     */
    public function serialize()
    {
        // Only serialize long term cookies and unexpired cookies
        return json_encode(array_map(function (Cookie $cookie) {
            return $cookie->toArray();
        }, $this->all(null, null, null, true, true)));
    }

    /**
     * Unserializes the cookie cookieJar
     */
    public function unserialize($data)
    {
        $data = json_decode($data, true);
        if (empty($data)) {
            $this->cookies = array();
        } else {
            $this->cookies = array_map(function (array $cookie) {
                return new Cookie($cookie);
            }, $data);
        }
    }

    /**
     * Returns the total number of stored cookies
     *
     * @return int
     */
    public function count()
    {
        return count($this->cookies);
    }

    /**
     * Returns an iterator
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->cookies);
    }

    /**
     * {@inheritdoc}
     */
    public function addCookiesFromResponse(Response $response)
    {
        if ($cookieHeader = $response->getSetCookie()) {

            $request = $response->getRequest();
            $parser = ParserRegistry::get('cookie');

            foreach ($cookieHeader as $cookie) {

                $parsed = $request
                    ? $parser->parseCookie($cookie, $request->getHost(), $request->getPath())
                    : $parser->parseCookie($cookie);

                if ($parsed) {
                    // Break up cookie v2 into multiple cookies
                    foreach ($parsed['cookies'] as $key => $value) {
                        $row = $parsed;
                        $row['name'] = $key;
                        $row['value'] = $value;
                        unset($row['cookies']);
                        $this->add(new Cookie($row));
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchingCookies(RequestInterface $request)
    {
        // Find cookies that match this request
        $cookies = $this->all($request->getHost(), $request->getPath());
        // Remove ineligible cookies
        foreach ($cookies as $index => $cookie) {
            if (!$cookie->matchesPort($request->getPort()) || ($cookie->getSecure() && $request->getScheme() != 'https')) {
                unset($cookies[$index]);
            }
        };

        return $cookies;
    }
}
