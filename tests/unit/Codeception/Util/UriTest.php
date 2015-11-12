<?php
namespace Codeception\Util;


class UriTest extends \Codeception\TestCase\Test
{
    // tests
    public function testUrlMerge()
    {
        $this->assertEquals(
            'http://codeception.com/quickstart',
            Uri::mergeUrls('http://codeception.com/hello', '/quickstart'),
            'merge paths'
        );

        $this->assertEquals(
            'http://codeception.com/hello/davert',
            Uri::mergeUrls('http://codeception.com/hello/world', 'davert'),
            'merge relative urls'
        );

        $this->assertEquals(
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
        $this->assertEquals('https://google.com/account/', Uri::mergeUrls('http://google.com/', 'https://google.com/account/'));
    }

    /**
     * @Issue https://github.com/Codeception/Codeception/pull/2499
     */
    public function testAppendAnchor()
    {
        $this->assertEquals('http://codeception.com/quickstart#anchor',
            Uri::appendPath('http://codeception.com/quickstart', '#anchor'));

        $this->assertEquals('http://codeception.com/quickstart#anchor',
            Uri::appendPath('http://codeception.com/quickstart#first', '#anchor'));
    }

    public function testAppendPath()
    {
        $this->assertEquals('http://codeception.com/quickstart/path',
            Uri::appendPath('http://codeception.com/quickstart', 'path'));

        $this->assertEquals('http://codeception.com/quickstart/path',
            Uri::appendPath('http://codeception.com/quickstart', '/path'));
    }

    public function testAppendEmptyPath()
    {
        $this->assertEquals('http://codeception.com/quickstart',
            Uri::appendPath('http://codeception.com/quickstart', ''));
    }

    public function testAppendPathRemovesQueryStringAndAnchor()
    {
        $this->assertEquals('http://codeception.com/quickstart',
            Uri::appendPath('http://codeception.com/quickstart?a=b#c', ''));
    }


}