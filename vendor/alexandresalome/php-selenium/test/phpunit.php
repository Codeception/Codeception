<?php
/*
 * This file is part of PHP Selenium Library.
 * (c) Alexandre Salomé <alexandre.salome@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/../vendor/autoload.php';

class SomeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Selenium\Browser
     */
    protected static $browser;

    public static function setUpBeforeClass()
    {
        $client        = new Selenium\Client('localhost', 4444);
        self::$browser = $client->getBrowser('http://alexandre-salome.fr');

        self::$browser->start();
    }

    public static function tearDownAfterClass()
    {
        self::$browser->stop();
    }

    /**
     * @dataProvider provideTestPage
     */
    public function testPage($menuText, $expectedPathinfo, $expectedTitle)
    {
        $browser = self::$browser;

        $browser
            ->open('/')
            ->click(Selenium\Locator::linkContaining($menuText))
            ->waitForPageToLoad(10000)
        ;
        $location = $browser->getLocation();
        $title    = $browser->getTitle();

        $this->assertRegexp('#'.preg_quote($expectedPathinfo).'$#', $location);
        $this->assertEquals($expectedTitle, $title);
    }

    public function provideTestPage()
    {
        return array(
            array('Blog',    '/blog',    'Blog | Alexandre Salomé'),
            array('CV',      '/cv',      'CV | Alexandre Salomé'),
            array('Contact', '/contact', 'Contact | Alexandre Salomé'),
        );
    }
}
