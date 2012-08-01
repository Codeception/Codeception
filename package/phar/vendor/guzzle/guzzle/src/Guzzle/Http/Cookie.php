<?php

namespace Guzzle\Http;

/**
 * Set-Cookie object
 */
class Cookie
{
    /**
     * @var array Cookie data
     */
    protected $data;

    /**
     * @param array $data Array of cookie data provided by a Cookie parser
     */
    public function __construct(array $data = array())
    {
        static $defaults = array(
            'name'        => '',
            'value'       => '',
            'domain'      => '',
            'path'        => '/',
            'expires'     => null,
            'max_age'     => 0,
            'comment'     => null,
            'comment_url' => null,
            'port'        => array(),
            'version'     => null,
            'secure'      => false,
            'discard'     => false,
            'http_only'   => false
        );

        $this->data = array_merge($defaults, $data);
        // Extract the expires value and turn it into a UNIX timestamp if needed
        if (!$this->getExpires() && $this->getMaxAge()) {
            // Calculate the expires date
            $this->setExpires(time() + (int) $this->getMaxAge());
        } elseif ($this->getExpires() && !is_numeric($this->getExpires())) {
            $this->setExpires(strtotime($this->getExpires()));
        }
    }

    /**
     * Get the cookie as an array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Get the cookie name
     *
     * @return string
     */
    public function getName()
    {
        return $this->data['name'];
    }

    /**
     * Set the cookie name
     *
     * @param string $name Cookie name
     *
     * @return Cookie
     */
    public function setName($name)
    {
        return $this->setData('name', $name);
    }

    /**
     * Get the cookie value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->data['value'];
    }

    /**
     * Set the cookie value
     *
     * @param string $value Cookie value
     *
     * @return Cookie
     */
    public function setValue($value)
    {
        return $this->setData('value', $value);
    }

    /**
     * Get the domain
     *
     * @return string|null
     */
    public function getDomain()
    {
        return $this->data['domain'];
    }

    /**
     * Set the domain of the cookie
     *
     * @param string $domain
     *
     * @return Cookie
     */
    public function setDomain($domain)
    {
        return $this->setData('domain', $domain);
    }

    /**
     * Get the path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->data['path'];
    }

    /**
     * Set the path of the cookie
     *
     * @param string $path Path of the cookie
     *
     * @return Cookie
     */
    public function setPath($path)
    {
        return $this->setData('path', $path);
    }

    /**
     * Maximum lifetime of the cookie in seconds
     *
     * @return int|null
     */
    public function getMaxAge()
    {
        return $this->data['max_age'];
    }

    /**
     * Set the max-age of the cookie
     *
     * @param int $maxAge Max age of the cookie in seconds
     *
     * @return Cookie
     */
    public function setMaxAge($maxAge)
    {
        return $this->setData('max_age', $maxAge);
    }

    /**
     * The UNIX timestamp when the cookie expires
     *
     * @return mixed
     */
    public function getExpires()
    {
        return $this->data['expires'];
    }

    /**
     * Set the unix timestamp for which the cookie will expire
     *
     * @param int $timestamp Unix timestamp
     *
     * @return Cookie
     */
    public function setExpires($timestamp)
    {
        return $this->setData('expires', $timestamp);
    }

    /**
     * Version of the cookie specification. RFC 2965 is 1
     *
     * @return mixed
     */
    public function getVersion()
    {
        return $this->data['version'];
    }

    /**
     * Set the cookie version
     *
     * @param string|int $version Version to set
     *
     * @return Cookie
     */
    public function setVersion($version)
    {
        return $this->setData('version', $version);
    }

    /**
     * Get whether or not this is a secure cookie
     *
     * @return null|bool
     */
    public function getSecure()
    {
        return $this->data['secure'];
    }

    /**
     * Set whether or not the cookie is secure
     *
     * @param bool $secure Set to true or false if secure
     *
     * @return Cookie
     */
    public function setSecure($secure)
    {
        return $this->setData('secure', (bool) $secure);
    }

    /**
     * Get whether or not this is a session cookie
     *
     * @return null|bool
     */
    public function getDiscard()
    {
        return $this->data['discard'];
    }

    /**
     * Set whether or not this is a session cookie
     *
     * @param bool $discard Set to true or false if this is a session cookie
     *
     * @return Cookie
     */
    public function setDiscard($discard)
    {
        return $this->setData('discard', $discard);
    }

    /**
     * Get the comment
     *
     * @return string|null
     */
    public function getComment()
    {
        return $this->data['comment'];
    }

    /**
     * Set the comment of the cookie
     *
     * @param string $comment Cookie comment
     *
     * @return Cookie
     */
    public function setComment($comment)
    {
        return $this->setData('comment', $comment);
    }

    /**
     * Get the comment URL of the cookie
     *
     * @return string|null
     */
    public function getCommentUrl()
    {
        return $this->data['comment_url'];
    }

    /**
     * Set the comment URL of the cookie
     *
     * @param string $commentUrl Cookie comment URL for more information
     *
     * @return Cookie
     */
    public function setCommentUrl($commentUrl)
    {
        return $this->setData('comment_url', $commentUrl);
    }

    /**
     * Get an array of acceptable ports this cookie can be used with
     *
     * @return array
     */
    public function getPorts()
    {
        return $this->data['port'];
    }

    /**
     * Set a list of acceptable ports this cookie can be used with
     *
     * @param array $ports Array of acceptable ports
     *
     * @return Cookie
     */
    public function setPorts(array $ports)
    {
        return $this->setData('port', $ports);
    }

    /**
     * Get whether or not this is an HTTP only cookie
     *
     * @return bool
     */
    public function getHttpOnly()
    {
        return $this->data['http_only'];
    }

    /**
     * Set whether or not this is an HTTP only cookie
     *
     * @param bool $httpOnly Set to true or false if this is HTTP only
     *
     * @return Cookie
     */
    public function setHttpOnly($httpOnly)
    {
        return $this->setData('http_only', $httpOnly);
    }

    /**
     * Get an array of extra cookie data
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->data['data'];
    }

    /**
     * Get a specific data point from the extra cookie data
     *
     * @param $name Name of the data point to retrieve
     *
     * @return null|string
     */
    public function getAttribute($name)
    {
        return array_key_exists($name, $this->data['data']) ? $this->data['data'][$name] : null;
    }

    /**
     * Set a cookie data attribute
     *
     * @param string $name  Name of the attribute to set
     * @param string $value Value to set
     *
     * @return Cookie
     */
    public function setAttribute($name, $value)
    {
        $this->data['data'][$name] = $value;

        return $this;
    }

    /**
     * Check if the cookie matches a path value
     *
     * @param string $path Path to check against
     *
     * @return bool
     */
    public function matchesPath($path)
    {
        return !$this->getPath() || 0 === stripos($path, $this->getPath());
    }

    /**
     * Check if the cookie matches a domain value
     *
     * @param string $domain Domain to check against
     *
     * @return bool
     */
    public function matchesDomain($domain)
    {
        return !$this->getDomain() || !strcasecmp($domain, $this->getDomain()) ||
            (strpos($this->getDomain(), '.') === 0 && preg_match('/' . preg_quote($this->getDomain()) . '$/i', $domain));
    }

    /**
     * Check if the cookie is compatible with a specific port
     *
     * @param int $port Port to check
     *
     * @return bool
     */
    public function matchesPort($port)
    {
        return count($this->getPorts()) == 0 || in_array($port, $this->getPorts());
    }

    /**
     * Check if the cookie is expired
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->getExpires() && time() > $this->getExpires();
    }

    /**
     * Set a value and return the cookie object
     *
     * @param string $key   Key to set
     * @param string $value Value to set
     *
     * @return Cookie
     */
    private function setData($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }
}
