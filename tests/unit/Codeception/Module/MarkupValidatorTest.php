<?php

use Codeception\Module\MarkupValidator;

class MarkupValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MarkupValidator
     */
    protected $module;

    protected function setUp()
    {
        $this->module = $this
            ->getMockBuilder('\Codeception\Module\MarkupValidator')
            ->setConstructorArgs(array(make_container()))
            ->setMethods(array(
                'getCurrentPageMarkup',
            ))
            ->getMock()
        ;

        $this->module->_initialize();
    }

    protected function tearDown()
    {
        $this->module->_cleanup();
    }

    public function testGetCurrentPageMarkupNoPhpBrowserNoWebDriver()
    {
        $this->module = $this
            ->getMockBuilder('\Codeception\Module\MarkupValidator')
            ->setConstructorArgs(array(make_container()))
            ->enableProxyingToOriginalMethods()
            ->getMock()
        ;

        $this->expectException('Exception');
        $this->assertValidMakup();
    }

    public function testGetCurrentPageMarkupOnlyPhpBrowser()
    {
        $phpBrowserMock = $this
            ->getMockBuilder('\Codeception\Module\PhpBrowser')
            ->setConstructorArgs(array(make_container()))
            ->setMethods(array(
                '_getPageSource',
            ))
            ->getMock()
        ;
        $phpBrowserMock
            ->method('_getPageSource')
            ->will($this->returnValue(
<<<HTML
                <!DOCTYPE HTML>
                <html>
                    <head>
                        <title>
                            A valid page.
                        </title>
                    </head>
                </html>
HTML
            ))
        ;

        $this->module = $this
            ->getMockBuilder('\Codeception\Module\MarkupValidator')
            ->setConstructorArgs(array(make_container()))
            ->setMethods(array(
                'hasModule',
                'getModule',
            ))
            ->getMock()
        ;
        $this->module
            ->method('hasModule')
            ->will($this->returnValueMap(array(
                array('PhpBrowser', true)
            )))
        ;
        $this->module
            ->method('getModule')
            ->will($this->returnValueMap(array(
                array('PhpBrowser', $phpBrowserMock)
            )))
        ;
        $this->module->_reconfigure(array(
            'pageSourceViewer' => 'PhpBrowser',
        ));

        $this->assertValidMakup();
    }

    public function testGetCurrentPageMarkupOnlyWebDriver()
    {
        $webDriverMock = $this
            ->getMockBuilder('\Codeception\Module\WebDriver')
            ->setConstructorArgs(array(make_container()))
            ->setMethods(array(
                '_getPageSource',
            ))
            ->getMock()
        ;
        $webDriverMock
            ->method('_getPageSource')
            ->will($this->returnValue(
<<<HTML
                <!DOCTYPE HTML>
                <html>
                    <head>
                        <title>
                            A valid page.
                        </title>
                    </head>
                </html>
HTML
            ))
        ;

        $this->module = $this
            ->getMockBuilder('\Codeception\Module\MarkupValidator')
            ->setConstructorArgs(array(make_container()))
            ->setMethods(array(
                'hasModule',
                'getModule',
            ))
            ->getMock()
        ;
        $this->module
            ->method('hasModule')
            ->will($this->returnValueMap(array(
                array('WebDriver', true)
            )))
        ;
        $this->module
            ->method('getModule')
            ->will($this->returnValueMap(array(
                array('WebDriver', $webDriverMock)
            )))
        ;
        $this->module->_reconfigure(array(
            'pageSourceViewer' => 'WebDriver',
        ));

        $this->assertValidMakup();
    }

    public function testValidateValidPage()
    {
        $this->mockGetCurrentPageMarkup(
<<<HTML
            <!DOCTYPE HTML>
            <html>
                <head>
                    <title>
                        A valid page.
                    </title>
                </head>
            </html>
HTML
        );

        $this->assertValidMakup(false);
    }

    public function testValidateInvalidPageDoNotIgnoreErrors()
    {
        $this->mockGetCurrentPageMarkup(
<<<HTML
            <!DOCTYPE HTML>
            <html>
                <head>
                </head>
            </html>
HTML
        );

        $expectedError = '/Element “head” is missing a required instance of child element “title”/';
        $this->assertInvalidMarkup($expectedError, false);
    }

    public function testValidateInvalidPageIgnoreErrors()
    {
        $this->mockGetCurrentPageMarkup(
<<<HTML
            <!DOCTYPE HTML>
            <html>
                <head>
                </head>
            </html>
HTML
        );

        $this->module->_reconfigure(array(
            'ignoredErrors' => array(
                '/Element “head” is missing a required instance of child element “title”/',
            ),
        ));

        $this->assertValidMakup();
    }

    public function testValidateInvalidPageIgnoreMultipleErrors()
    {
        $this->mockGetCurrentPageMarkup(
<<<HTML
            <!DOCTYPE HTML>
            <html>
                <head>
                </head>
                <body>
                    <form>
                        <button role="button">
                        </button>
                    </form>
                </body>
            </html>
HTML
        );

        $this->module->_reconfigure(array(
            'ignoredErrors' => array(
                '/Element “head” is missing a required instance of child element “title”/',
                '/The “button” role is unnecessary for element “button”/',
            ),
        ));

        $this->assertValidMakup();
    }

    public function testValidatePageWithWarningsDoNotIgnoreWarnings()
    {
        $this->mockGetCurrentPageMarkup(
<<<HTML
            <!DOCTYPE HTML>
            <html>
                <head>
                    <title>
                        A page with a warning.
                    </title>
                </head>
                <body>
                    <form>
                        <button role="button">
                        </button>
                    </form>
                </body>
            </html>
HTML
        );

        $expectedError = '/The “button” role is unnecessary for element “button”/';
        $this->assertInvalidMarkup($expectedError, false);
    }

    public function testValidatePageWithWarningsIgnoreWarningsLocal()
    {
        $this->mockGetCurrentPageMarkup(
<<<HTML
            <!DOCTYPE HTML>
            <html>
                <head>
                    <title>
                        A page with a warning.
                    </title>
                </head>
                <body>
                    <form>
                        <button role="button" type="submit">
                        </button>
                    </form>
                </body>
            </html>
HTML
        );

        $this->assertValidMakup(true);
    }

    public function testValidatePageWithWarningsIgnoreWarningsModuleWide()
    {
        $this->mockGetCurrentPageMarkup(
<<<HTML
            <!DOCTYPE HTML>
            <html>
                <head>
                    <title>
                        A page with a warning.
                    </title>
                </head>
                <body>
                    <form>
                        <button role="button" type="submit">
                        </button>
                    </form>
                </body>
            </html>
HTML
        );

        $this->module->_reconfigure(array(
            'ignoreWarnings' => true,
        ));

        $this->assertValidMakup();
    }

    public function testValidatePageWithWarningsIgnoreWarningsLocalOverrideModuleWideFalse()
    {
        $this->mockGetCurrentPageMarkup(
<<<HTML
            <!DOCTYPE HTML>
            <html>
                <head>
                    <title>
                        A page with a warning.
                    </title>
                </head>
                <body>
                    <form>
                        <button role="button" type="submit">
                        </button>
                    </form>
                </body>
            </html>
HTML
        );

        $this->module->_reconfigure(array(
            'ignoreWarnings' => false,
        ));

        $this->assertValidMakup(true);
    }

    public function testValidatePageWithWarningsIgnoreWarningsLocalOverrideModuleWideTrue()
    {
        $this->mockGetCurrentPageMarkup(
<<<HTML
            <!DOCTYPE HTML>
            <html>
                <head>
                    <title>
                        A page with a warning.
                    </title>
                </head>
                <body>
                    <form>
                        <button role="button" type="submit">
                        </button>
                    </form>
                </body>
            </html>
HTML
        );

        $this->module->_reconfigure(array(
            'ignoreWarnings' => true,
        ));

        $this->assertInvalidMarkup('/The “button” role is unnecessary for element “button”/', false);
    }

    private function mockGetCurrentPageMarkup($markup)
    {
        $this->module
            ->method('getCurrentPageMarkup')
            ->will($this->returnValue($markup))
        ;
    }

    private function assertValidMakup($ignoreWarnings = null)
    {
        $this->module->validateMarkup($ignoreWarnings);
    }

    private function assertInvalidMarkup($expectedError = null, $ignoreWarnings = null)
    {
        $errorReported = false;

        try {
            $this->module->validateMarkup($ignoreWarnings);
        } catch (Exception $exception) {
            if ($expectedError !== null) {
                $actualError = $exception->getMessage();
                $errorsMatch = preg_match($expectedError, $actualError) === 1;
                if (!$errorsMatch) {
                    $this->fail('Expected error was not reported.');
                }
            }
            $errorReported = true;
        }

        if (!$errorReported) {
            $this->fail('No errors were reported.');
        }
    }
}
