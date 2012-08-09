<?php

namespace Tests\Behat\Mink;

use Behat\Mink\WebAssert;

/**
 * @group unittest
 */
class WebAssertTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $session;
    /**
     * @var WebAssert
     */
    private $assert;

    public function setUp()
    {
        $this->session = $this->getMockBuilder('Behat\\Mink\\Session')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assert  = new WebAssert($this->session);
    }

    public function testAddressEquals()
    {
        $this->session
            ->expects($this->exactly(2))
            ->method('getCurrentUrl')
            ->will($this->returnValue('http://example.com/script.php/sub/url'))
        ;

        $this->assertCorrectAssertion('addressEquals', array('/sub/url'));
        $this->assertWrongAssertion(
            'addressEquals', array('sub_url'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'Current page is "/sub/url", but "sub_url" expected.'
        );
    }

    public function testAddressNotEquals()
    {
        $this->session
            ->expects($this->exactly(2))
            ->method('getCurrentUrl')
            ->will($this->returnValue('http://example.com/script.php/sub/url'))
        ;

        $this->assertCorrectAssertion('addressNotEquals', array('sub_url'));
        $this->assertWrongAssertion(
            'addressNotEquals', array('/sub/url'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'Current page is "/sub/url", but should not be.'
        );
    }

    public function testAddressMatches()
    {
        $this->session
            ->expects($this->exactly(2))
            ->method('getCurrentUrl')
            ->will($this->returnValue('http://example.com/script.php/sub/url'))
        ;

        $this->assertCorrectAssertion('addressMatches', array('/su.*rl/'));
        $this->assertWrongAssertion(
            'addressMatches', array('/suburl/'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'Current page "/sub/url" does not match the regex "/suburl/".'
        );
    }

    /**
     * @covers Behat\Mink\WebAssert::cookieExists
     */
    public function testCookieExists()
    {
        $this->session->
            expects($this->any())->
            method('getCookie')->
            will($this->returnValueMap(
                array(
                    array('foo', '1'),
                    array('bar', null),
                )
            ));

        $this->assertCorrectAssertion('cookieExists', array('foo'));
        $this->assertWrongAssertion(
            'cookieExists', array('bar'),
            'Behat\Mink\Exception\ExpectationException',
            'Cookie "bar" is not set, but should be.'
        );
    }

    public function testStatusCodeEquals()
    {
        $this->session
            ->expects($this->exactly(2))
            ->method('getStatusCode')
            ->will($this->returnValue(200))
        ;

        $this->assertCorrectAssertion('statusCodeEquals', array(200));
        $this->assertWrongAssertion(
            'statusCodeEquals', array(404),
            'Behat\\Mink\\Exception\\ExpectationException',
            'Current response status code is 200, but 404 expected.'
        );
    }

    public function testPageTextContains()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('getText')
            ->will($this->returnValue('Some page text'))
        ;

        $this->assertCorrectAssertion('pageTextContains', array('PAGE text'));
        $this->assertWrongAssertion(
            'pageTextContains', array('html text'),
            'Behat\\Mink\\Exception\\ResponseTextException',
            'The text "html text" was not found anywhere in the text of the current page.'
        );
    }

    public function testPageTextNotContains()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('getText')
            ->will($this->returnValue('Some html text'))
        ;

        $this->assertCorrectAssertion('pageTextNotContains', array('PAGE text'));
        $this->assertWrongAssertion(
            'pageTextNotContains', array('HTML text'),
            'Behat\\Mink\\Exception\\ResponseTextException',
            'The text "HTML text" appears in the text of this page, but it should not.'
        );
    }

    public function testPageTextMatches()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('getText')
            ->will($this->returnValue('Some page text'))
        ;

        $this->assertCorrectAssertion('pageTextMatches', array('/PA.E/i'));
        $this->assertWrongAssertion(
            'pageTextMatches', array('/html/'),
            'Behat\\Mink\\Exception\\ResponseTextException',
            'The pattern /html/ was not found anywhere in the text of the current page.'
        );
    }

    public function testPageTextNotMatches()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('getText')
            ->will($this->returnValue('Some html text'))
        ;

        $this->assertCorrectAssertion('pageTextNotMatches', array('/PA.E/i'));
        $this->assertWrongAssertion(
            'pageTextNotMatches', array('/HTML/i'),
            'Behat\\Mink\\Exception\\ResponseTextException',
            'The pattern /HTML/i was found in the text of the current page, but it should not.'
        );
    }


    public function testResponseContains()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('getContent')
            ->will($this->returnValue('Some page text'))
        ;

        $this->assertCorrectAssertion('responseContains', array('PAGE text'));
        $this->assertWrongAssertion(
            'responseContains', array('html text'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'The string "html text" was not found anywhere in the HTML response of the current page.'
        );
    }

    public function testResponseNotContains()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('getContent')
            ->will($this->returnValue('Some html text'))
        ;

        $this->assertCorrectAssertion('responseNotContains', array('PAGE text'));
        $this->assertWrongAssertion(
            'responseNotContains', array('HTML text'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'The string "HTML text" appears in the HTML response of this page, but it should not.'
        );
    }

    public function testResponseMatches()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('getContent')
            ->will($this->returnValue('Some page text'))
        ;

        $this->assertCorrectAssertion('responseMatches', array('/PA.E/i'));
        $this->assertWrongAssertion(
            'responseMatches', array('/html/'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'The pattern /html/ was not found anywhere in the HTML response of the page.'
        );
    }

    public function testResponseNotMatches()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('getContent')
            ->will($this->returnValue('Some html text'))
        ;

        $this->assertCorrectAssertion('responseNotMatches', array('/PA.E/i'));
        $this->assertWrongAssertion(
            'responseNotMatches', array('/HTML/i'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'The pattern /HTML/i was found in the HTML response of the page, but it should not.'
        );
    }

    public function testElementsCount()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('findAll')
            ->with('css', 'h2 > span')
            ->will($this->returnValue(array(1, 2)))
        ;

        $this->assertCorrectAssertion('elementsCount', array('css', 'h2 > span', 2));
        $this->assertWrongAssertion(
            'elementsCount', array('css', 'h2 > span', 3),
            'Behat\\Mink\\Exception\\ExpectationException',
            '2 elements matching css "h2 > span" found on the page, but should be 3.'
        );
    }

    public function testElementExists()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('find')
            ->with('css', 'h2 > span')
            ->will($this->onConsecutiveCalls(1, null))
        ;

        $this->assertCorrectAssertion('elementExists', array('css', 'h2 > span'));
        $this->assertWrongAssertion(
            'elementExists', array('css', 'h2 > span'),
            'Behat\\Mink\\Exception\\ElementNotFoundException',
            'Element matching css "h2 > span" not found.'
        );
    }

    public function testElementNotExists()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('find')
            ->with('css', 'h2 > span')
            ->will($this->onConsecutiveCalls(null, 1))
        ;

        $this->assertCorrectAssertion('elementNotExists', array('css', 'h2 > span'));
        $this->assertWrongAssertion(
            'elementNotExists', array('css', 'h2 > span'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'An element matching css "h2 > span" appears on this page, but it should not.'
        );
    }

    public function testElementTextContains()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $element = $this->getMockBuilder('Behat\\Mink\\Element\\NodeElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('find')
            ->with('css', 'h2 > span')
            ->will($this->returnValue($element))
        ;

        $element
            ->expects($this->exactly(2))
            ->method('getText')
            ->will($this->returnValue('element text'))
        ;

        $this->assertCorrectAssertion('elementTextContains', array('css', 'h2 > span', 'text'));
        $this->assertWrongAssertion(
            'elementTextContains', array('css', 'h2 > span', 'html'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'The text "html" was not found in the text of the element matching css "h2 > span".'
        );
    }

    public function testElementTextNotContains()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $element = $this->getMockBuilder('Behat\\Mink\\Element\\NodeElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('find')
            ->with('css', 'h2 > span')
            ->will($this->returnValue($element))
        ;

        $element
            ->expects($this->exactly(2))
            ->method('getText')
            ->will($this->returnValue('element text'))
        ;

        $this->assertCorrectAssertion('elementTextNotContains', array('css', 'h2 > span', 'html'));
        $this->assertWrongAssertion(
            'elementTextNotContains', array('css', 'h2 > span', 'text'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'The text "text" appears in the text of the element matching css "h2 > span", but it should not.'
        );
    }

    public function testElementContains()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $element = $this->getMockBuilder('Behat\\Mink\\Element\\NodeElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('find')
            ->with('css', 'h2 > span')
            ->will($this->returnValue($element))
        ;

        $element
            ->expects($this->exactly(2))
            ->method('getHtml')
            ->will($this->returnValue('element html'))
        ;

        $this->assertCorrectAssertion('elementContains', array('css', 'h2 > span', 'html'));
        $this->assertWrongAssertion(
            'elementContains', array('css', 'h2 > span', 'text'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'The string "text" was not found in the HTML of the element matching css "h2 > span".'
        );
    }

    public function testElementNotContains()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $element = $this->getMockBuilder('Behat\\Mink\\Element\\NodeElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('find')
            ->with('css', 'h2 > span')
            ->will($this->returnValue($element))
        ;

        $element
            ->expects($this->exactly(2))
            ->method('getHtml')
            ->will($this->returnValue('element html'))
        ;

        $this->assertCorrectAssertion('elementNotContains', array('css', 'h2 > span', 'text'));
        $this->assertWrongAssertion(
            'elementNotContains', array('css', 'h2 > span', 'html'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'The string "html" appears in the HTML of the element matching css "h2 > span", but it should not.'
        );
    }

    public function testFieldExists()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $element = $this->getMockBuilder('Behat\\Mink\\Element\\NodeElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('findField')
            ->with('username')
            ->will($this->onConsecutiveCalls($element, null))
        ;

        $this->assertCorrectAssertion('fieldExists', array('username'));
        $this->assertWrongAssertion(
            'fieldExists', array('username'),
            'Behat\\Mink\\Exception\\ElementNotFoundException',
            'Form field with id|name|label|value "username" not found.'
        );
    }

    public function testFieldNotExists()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $element = $this->getMockBuilder('Behat\\Mink\\Element\\NodeElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('findField')
            ->with('username')
            ->will($this->onConsecutiveCalls(null, $element))
        ;

        $this->assertCorrectAssertion('fieldNotExists', array('username'));
        $this->assertWrongAssertion(
            'fieldNotExists', array('username'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'A field "username" appears on this page, but it should not.'
        );
    }

    public function testFieldValueEquals()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $element = $this->getMockBuilder('Behat\\Mink\\Element\\NodeElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('findField')
            ->with('username')
            ->will($this->returnValue($element))
        ;

        $element
            ->expects($this->exactly(2))
            ->method('getValue')
            ->will($this->returnValue(234))
        ;

        $this->assertCorrectAssertion('fieldValueEquals', array('username', 234));
        $this->assertWrongAssertion(
            'fieldValueEquals', array('username', 235),
            'Behat\\Mink\\Exception\\ExpectationException',
            'The field "username" value is "234", but "235" expected.'
        );
    }

    public function testFieldValueNotEquals()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $element = $this->getMockBuilder('Behat\\Mink\\Element\\NodeElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('findField')
            ->with('username')
            ->will($this->returnValue($element))
        ;

        $element
            ->expects($this->exactly(2))
            ->method('getValue')
            ->will($this->returnValue(235))
        ;

        $this->assertCorrectAssertion('fieldValueNotEquals', array('username', 234));
        $this->assertWrongAssertion(
            'fieldValueNotEquals', array('username', 235),
            'Behat\\Mink\\Exception\\ExpectationException',
            'The field "username" value is "235", but it should not be.'
        );
    }

    public function testCheckboxChecked()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $element = $this->getMockBuilder('Behat\\Mink\\Element\\NodeElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('findField')
            ->with('remember_me')
            ->will($this->returnValue($element))
        ;

        $element
            ->expects($this->exactly(2))
            ->method('isChecked')
            ->will($this->onConsecutiveCalls(true, false))
        ;

        $this->assertCorrectAssertion('checkboxChecked', array('remember_me'));
        $this->assertWrongAssertion(
            'checkboxChecked', array('remember_me'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'Checkbox "remember_me" is not checked, but it should be.'
        );
    }

    public function testCheckboxNotChecked()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $element = $this->getMockBuilder('Behat\\Mink\\Element\\NodeElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('findField')
            ->with('remember_me')
            ->will($this->returnValue($element))
        ;

        $element
            ->expects($this->exactly(2))
            ->method('isChecked')
            ->will($this->onConsecutiveCalls(false, true))
        ;

        $this->assertCorrectAssertion('checkboxNotChecked', array('remember_me'));
        $this->assertWrongAssertion(
            'checkboxNotChecked', array('remember_me'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'Checkbox "remember_me" is checked, but it should not be.'
        );
    }

    protected function assertCorrectAssertion($assertion, $arguments)
    {
        try {
            call_user_func_array(array($this->assert, $assertion), $arguments);
        } catch (\Exception $e) {
            $this->fail('Correct assertion should not throw an exception: '.$e->getMessage());
        }
    }

    protected function assertWrongAssertion($assertion, $arguments, $exceptionClass, $exceptionMessage)
    {
        try {
            call_user_func_array(array($this->assert, $assertion), $arguments);
            $this->fail('Wrong assertion should throw an exception');
        } catch (\Exception $e) {
            $this->assertInstanceOf($exceptionClass, $e);
            $this->assertSame($exceptionMessage, $e->getMessage());
        }
    }
}