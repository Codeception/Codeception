<?php
namespace Codeception\Util;

use GuzzleHttp\Psr7\Uri as Psr7Uri;

class Uri
{
    /**
     * Merges the passed $add argument onto $base.
     *
     * If a relative URL is passed as the 'path' part of the $add url
     * array, the relative URL is mapped using the base 'path' part as
     * its base.
     *
     * @param string $baseUri the base URL
     * @param string $uri the URL to merge
     * @return array the merged array
     */
    public static function mergeUrls($baseUri, $uri)
    {
        $base = new Psr7Uri($baseUri);
        $parts = parse_url($uri);
        if ($parts === false) {
            throw new \InvalidArgumentException("Invalid URI $uri");
        }

        if (isset($parts['host']) and isset($parts['scheme'])) {
            // if this is an absolute url, replace with it
            return $uri;
        }

        if (isset($parts['host'])) {
            $base = $base->withHost($parts['host']);
            $base = $base->withPath('');
            $base = $base->withQuery('');
            $base = $base->withFragment('');
        }
        if (isset($parts['path'])) {
            $path = $parts['path'];
            if ($base->getPath() && (strpos($path, '/') !== 0) && !empty($path)) {
                // if it ends with a slash, relative paths are below it
                if (preg_match('~/$~', $base->getPath())) {
                    $path = $base->getPath() . $path;
                } else {
                    // remove double slashes
                    $dir = rtrim(dirname($base->getPath()), '\\/');
                    $path = $dir . '/' . $path;
                }
            }
            $base = $base->withPath($path);
            $base = $base->withQuery('');
            $base = $base->withFragment('');
        }
        if (isset($parts['query'])) {
            $base = $base->withQuery($parts['query']);
            $base = $base->withFragment('');
        }
        if (isset($parts['fragment'])) {
            $base = $base->withFragment($parts['fragment']);
        }

        return (string) $base;
    }

    /**
     * Retrieve /path?query#fragment part of URL
     * @param $url
     * @return string
     */
    public static function retrieveUri($url)
    {
        $uri = new Psr7Uri($url);
        return (string)(new Psr7Uri())
            ->withPath($uri->getPath())
            ->withQuery($uri->getQuery())
            ->withFragment($uri->getFragment());
    }

    public static function retrieveHost($url)
    {
        $urlParts = parse_url($url);
        if (!isset($urlParts['host']) or !isset($urlParts['scheme'])) {
            throw new \InvalidArgumentException("Wrong URL passes, host and scheme not set");
        }
        $host = $urlParts['scheme'] . '://' . $urlParts['host'];
        if (isset($urlParts['port'])) {
            $host .= ':' . $urlParts['port'];
        }
        return $host;
    }

    public static function appendPath($url, $path)
    {
        $uri = new Psr7Uri($url);
        $cutUrl = (string)$uri->withQuery('')->withFragment('');

        if ($path === '' || $path[0] === '#') {
            return $cutUrl . $path;
        } else {
            return rtrim($cutUrl, '/') . '/'  . ltrim($path, '/');
        }
    }
}
