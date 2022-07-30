<?php
namespace Codeception\Util;

class UriTest extends \Codeception\Test\Unit
{
    // tests
    public function testUrlMerge()
    {
        $this->assertEquals(
            'https://codeception.com/quickstart',
            Uri::mergeUrls('https://codeception.com/hello', '/quickstart'),
            'merge paths'
        );

        $this->assertEquals(
            'https://codeception.com/hello/davert',
            Uri::mergeUrls('https://codeception.com/hello/world', 'davert'),
            'merge relative urls'
        );

        $this->assertEquals(
            'https://github.com/codeception/codeception',
            Uri::mergeUrls('https://codeception.com/hello/world', 'https://github.com/codeception/codeception'),
            'merge absolute urls'
        );
    }

    /**
     * @Issue https://github.com/Codeception/Codeception/pull/2141
     */
    public function testMergingScheme()
    {
        $this->assertEquals(
            'https://google.com/account/',
            Uri::mergeUrls('https://google.com/', 'https://google.com/account/')
        );
        $this->assertEquals('https://facebook.com/', Uri::mergeUrls('https://google.com/test/', '//facebook.com/'));
        $this->assertEquals(
            'https://facebook.com/#anchor2',
            Uri::mergeUrls('https://google.com/?param=1#anchor', '//facebook.com/#anchor2')
        );
    }

    /**
     * @Issue https://github.com/Codeception/Codeception/pull/2841
     */
    public function testMergingPath()
    {
        $this->assertEquals('/form/?param=1#anchor', Uri::mergeUrls('/form/?param=1', '#anchor'));
        $this->assertEquals('/form/?param=1#anchor2', Uri::mergeUrls('/form/?param=1#anchor1', '#anchor2'));
        $this->assertEquals('/form/?param=2', Uri::mergeUrls('/form/?param=1#anchor', '?param=2'));
        $this->assertEquals('/page/', Uri::mergeUrls('/form/?param=1#anchor', '/page/'));
    }   

    /**
     * @Issue https://github.com/Codeception/Codeception/pull/4847
     */
    public function testMergingNonParsingPath()
    {
        $this->assertEquals('/3.0/en/index/page:5', Uri::mergeUrls('https://cakephp.org/', '/3.0/en/index/page:5'));
    }

    /**
     * @Issue https://github.com/Codeception/Codeception/pull/2499
     */
    public function testAppendAnchor()
    {
        $this->assertEquals(
            'https://codeception.com/quickstart#anchor',
            Uri::appendPath('https://codeception.com/quickstart', '#anchor')
        );

        $this->assertEquals(
            'https://codeception.com/quickstart#anchor',
            Uri::appendPath('https://codeception.com/quickstart#first', '#anchor')
        );
    }

    public function testAppendPath()
    {
        $this->assertEquals(
            'https://codeception.com/quickstart/path',
            Uri::appendPath('https://codeception.com/quickstart', 'path')
        );

        $this->assertEquals(
            'https://codeception.com/quickstart/path',
            Uri::appendPath('https://codeception.com/quickstart', '/path')
        );
    }

    public function testAppendEmptyPath()
    {
        $this->assertEquals(
            'https://codeception.com/quickstart',
            Uri::appendPath('https://codeception.com/quickstart', '')
        );
    }

    public function testAppendPathRemovesQueryStringAndAnchor()
    {
        $this->assertEquals(
            'https://codeception.com/quickstart',
            Uri::appendPath('https://codeception.com/quickstart?a=b#c', '')
        );
    }

    public function testMergeUrlsWhenBaseUriHasNoTrailingSlashAndUriPathHasNoLeadingSlash()
    {
        $this->assertEquals(
            'https://codeception.com/test',
            Uri::mergeUrls('https://codeception.com', 'test'));
    }

    public function testMergeUrlsWhenBaseUriEndsWithSlashButUriPathHasNoLeadingSlash()
    {
        $this->assertEquals(
            'https://codeception.com/test',
            Uri::mergeUrls('https://codeception.com/', 'test'));
    }

}
