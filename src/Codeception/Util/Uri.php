<?php

declare(strict_types=1);

namespace Codeception\Util;

use GuzzleHttp\Psr7\Uri as Psr7Uri;
use InvalidArgumentException;

use function dirname;
use function ltrim;
use function parse_url;
use function preg_match;
use function rtrim;

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
     * @return string the merged array
     */
    public static function mergeUrls(string $baseUri, string $uri): string
    {
        $base = new Psr7Uri($baseUri);
        $parts = parse_url($uri);

        //If the relative URL does not parse, attempt to parse the entire URL.
        //PHP Known bug ( https://bugs.php.net/bug.php?id=70942 )
        if ($parts === false) {
            $parts = parse_url($base . $uri);
        }

        if ($parts === false) {
            throw new InvalidArgumentException("Invalid URI {$uri}");
        }

        if (isset($parts['host']) && isset($parts['scheme'])) {
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
            $basePath = $base->getPath();
            if ((!str_starts_with($path, '/')) && !empty($path)) {
                if ($basePath !== '') {
                    // if it ends with a slash, relative paths are below it
                    if (preg_match('#/$#', $basePath)) {
                        $path = $basePath . $path;
                    } else {
                        // remove double slashes
                        $dir = rtrim(dirname($basePath), '\\/');
                        $path = $dir . '/' . $path;
                    }
                } else {
                    $path = '/' . ltrim($path, '/');
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

        return (string)$base;
    }

    /**
     * Retrieve /path?query#fragment part of URL
     */
    public static function retrieveUri(string $url): string
    {
        $uri = new Psr7Uri($url);
        return (string)(new Psr7Uri())
            ->withPath($uri->getPath())
            ->withQuery($uri->getQuery())
            ->withFragment($uri->getFragment());
    }

    public static function retrieveHost(string $url): string
    {
        $urlParts = parse_url($url);
        if (!isset($urlParts['host']) || !isset($urlParts['scheme'])) {
            throw new InvalidArgumentException("Wrong URL passes, host and scheme not set");
        }
        $host = $urlParts['scheme'] . '://' . $urlParts['host'];
        if (isset($urlParts['port'])) {
            $host .= ':' . $urlParts['port'];
        }
        return $host;
    }

    public static function appendPath(string $url, string $path): string
    {
        $uri = new Psr7Uri($url);
        $cutUrl = (string)$uri->withQuery('')->withFragment('');

        if ($path === '' || $path[0] === '#') {
            return $cutUrl . $path;
        }

        return rtrim($cutUrl, '/') . '/' . ltrim($path, '/');
    }
}
