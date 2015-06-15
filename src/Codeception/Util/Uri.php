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
        }
        if (isset($parts['query'])) {
            $base = $base->withQuery($parts['query']);
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


    public static function appendPath($url, $path)
    {
        $uri = new Psr7Uri($url);
        $cutUrl = (string)$uri->withQuery('')->withFragment('');
        return rtrim($cutUrl,'/') . '/'  . ltrim($path, '/');
    }
}