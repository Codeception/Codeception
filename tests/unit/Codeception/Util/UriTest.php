<?php

declare(strict_types=1);

namespace Codeception\Util;

class UriTest extends \Codeception\Test\Unit
{
    // tests
    public function testUrlMerge()
    {
        $this->assertSame(
            'http://codeception.com/quickstart',
            Uri::mergeUrls('http://codeception.com/hello', '/quickstart'),
            'merge paths'
        );

        $this->assertSame(
            'http://codeception.com/hello/davert',
            Uri::mergeUrls('http://codeception.com/hello/world', 'davert'),
            'merge relative urls'
        );

        $this->assertSame(
            'https://github.com/codeception/codeception',
            Uri::mergeUrls('http://codeception.com/hello/world', 'https://github.com/codeception/codeception'),
            'merge absolute urls'
        );
    }

    /**
     * @Issue https://github.com/Codeception/Codeception/pull/2141
     */
    public function testMergingScheme()
    {
        $this->assertSame(
            'https://google.com/account/',
            Uri::mergeUrls('http://google.com/', 'https://google.com/account/')
        );
        $this->assertSame('https://facebook.com/', Uri::mergeUrls('https://google.com/test/', '//facebook.com/'));
        $this->assertSame(
            'https://facebook.com/#anchor2',
            Uri::mergeUrls('https://google.com/?param=1#anchor', '//facebook.com/#anchor2')
        );
    }

    /**
     * @Issue https://github.com/Codeception/Codeception/pull/2841
     */
    public function testMergingPath()
    {
        $this->assertSame('/form/?param=1#anchor', Uri::mergeUrls('/form/?param=1', '#anchor'));
        $this->assertSame('/form/?param=1#anchor2', Uri::mergeUrls('/form/?param=1#anchor1', '#anchor2'));
        $this->assertSame('/form/?param=2', Uri::mergeUrls('/form/?param=1#anchor', '?param=2'));
        $this->assertSame('/page/', Uri::mergeUrls('/form/?param=1#anchor', '/page/'));
    }   

    /**
     * @Issue https://github.com/Codeception/Codeception/pull/4847
     */
    public function testMergingNonParsingPath()
    {
        $this->assertSame('/3.0/en/index/page:5', Uri::mergeUrls('https://cakephp.org/', '/3.0/en/index/page:5'));
    }

    /**
     * @Issue https://github.com/Codeception/Codeception/pull/2499
     */
    public function testAppendAnchor()
    {
        $this->assertSame(
            'http://codeception.com/quickstart#anchor',
            Uri::appendPath('http://codeception.com/quickstart', '#anchor')
        );

        $this->assertSame(
            'http://codeception.com/quickstart#anchor',
            Uri::appendPath('http://codeception.com/quickstart#first', '#anchor')
        );
    }

    public function testAppendPath()
    {
        $this->assertSame(
            'http://codeception.com/quickstart/path',
            Uri::appendPath('http://codeception.com/quickstart', 'path')
        );

        $this->assertSame(
            'http://codeception.com/quickstart/path',
            Uri::appendPath('http://codeception.com/quickstart', '/path')
        );
    }

    public function testAppendEmptyPath()
    {
        $this->assertSame(
            'http://codeception.com/quickstart',
            Uri::appendPath('http://codeception.com/quickstart', '')
        );
    }

    public function testAppendPathRemovesQueryStringAndAnchor()
    {
        $this->assertSame(
            'http://codeception.com/quickstart',
            Uri::appendPath('http://codeception.com/quickstart?a=b#c', '')
        );
    }

    public function testMergeUrlsWhenBaseUriHasNoTrailingSlashAndUriPathHasNoLeadingSlash()
    {
        $this->assertSame(
            'http://codeception.com/test',
            Uri::mergeUrls('http://codeception.com', 'test'));
    }

    public function testMergeUrlsWhenBaseUriEndsWithSlashButUriPathHasNoLeadingSlash()
    {
        $this->assertSame(
            'http://codeception.com/test',
            Uri::mergeUrls('http://codeception.com/', 'test'));
    }

}
