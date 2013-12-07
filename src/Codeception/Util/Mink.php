<?php

namespace Codeception\Util;

use Behat\Mink\Element\Element;
use Behat\Mink\Element\NodeElement;
use Codeception\Exception\ElementNotFound;
use Codeception\Exception\Module as ModuleException;
use Codeception\Exception\ModuleConfig as ModuleConfigException;
use Codeception\Module;
use Codeception\PHPUnit\Constraint\Page;
use Codeception\TestCase;
use Symfony\Component\CssSelector\CssSelector;

abstract class Mink extends Module implements RemoteInterface, WebInterface
{
    /**
     * @var \Behat\Mink\Session
     */
    public $session = null;

    public function _initialize()
    {
        if (!$this->session) {
            throw new ModuleException(
                __CLASS__,
                'Module is not initialized. Mink session is not started in _initialize method of module.'
            );
        };
        try {
            $this->session->start();
            $this->session->visit(rtrim($this->config['url'], '/'));
            $this->session->stop();
        } catch (\Exception $e) {
            throw new ModuleConfigException(
                __CLASS__,
                'Provided URL can\'t be accessed by this driver.' . $e->getMessage()
            );
        }
    }

    public function _before(TestCase $test)
    {
        $this->session->start();
    }

    public function _after(TestCase $test)
    {
        $this->session->stop();
    }

    public function _getUrl()
    {
        if (!isset($this->config['url'])) {
            throw new ModuleConfigException(
                __CLASS__,
                'Module connection failure. The URL for client can\'t be retrieved'
            );
        }

        return $this->config['url'];
    }

    public function _setCookie($cookie, $value)
    {
        $this->session->setCookie($cookie, $value);
    }

    /**
     *
     * @param type $cookie
     * @return type
     */
    public function _getCookie($cookie)
    {
        $value = $this->session->getCookie($cookie);
        if (is_null($value)) {
            // try to parse headers because of bug in
            // \Behat\Mink\Driver\BrowserKitDriver::getCookie
            $value = $this->_parseCookieFromHeaders($cookie);
        }
        return $value;
    }


    /**
     *
     * this method fixes the following
     *
     * @see \Behat\Mink\Driver\BrowserKitDriver::getCookie
     *   Note that the following doesn't work well because
     *   Symfony\Component\BrowserKit\CookieJar stores cookies by name,
     *   path, AND domain and if you don't fill them all in correctly then
     *   you won't get the value that you're expecting.
     *
     *
     *
     * @param string $name
     * @return null|string
     */
    private function _parseCookieFromHeaders($name)
    {
        try {
            $headers = $this->session->getResponseHeaders();
        } catch (\Behat\Mink\Exception\UnsupportedDriverActionException $e) {
            return null;
        }

        if (!is_array($headers) || empty($headers) || !isset($headers['set-cookie'])) {
            return null;
        }

        foreach ($headers['set-cookie'] as $cookieString) {
            if (!stripos($cookieString, $name) === 0) {
                continue;
            }
            $cookiePieces = explode(';', $cookieString);
            if (!$cookiePieces) {
                return null;
            }
            $cookieKeyValue = explode('=', $cookiePieces[0]);
            if (!isset($cookieKeyValue[1])) {
                return null;
            }
            return $cookieKeyValue[1];

        }
        return null;
    }

    public function _getResponseCode()
    {
        return $this->session->getStatusCode();
    }

    public function _sendRequest($url)
    {
        $this->session->visit($url);
        return $this->session->getDriver()->getContent();
    }

    /**
     * Opens the page.
     *
     * @param $page
     */
    public function amOnPage($page)
    {
        $host = rtrim($this->config['url'], '/');
        $page = ltrim($page, '/');
        $this->session->visit($host . '/' . $page);
    }

    public function amOnSubdomain($subdomain)
    {
        $url = $this->config['url'];
        $url = preg_replace('~(https?:\/\/)(.*\.)(.*\.)~', "$1$3", $url); // removing current subdomain
        $url = preg_replace('~(https?:\/\/)(.*)~', "$1$subdomain.$2", $url); // inserting new
        $this->_reconfigure(array('url' => $url));
    }

    /**
     * @param string $text
     * @param string $selector
     *
     * @return void
     */
    public function dontSee($text, $selector = null)
    {
        try {
            $res = $this->proceedSee($text, $selector);
        } catch (ElementNotFound $e) {
            $this->assertFalse(false, "$selector not found on page");
            return;
        }
        call_user_func_array(array($this, 'assertPageNotContains'), $res);
    }

    public function see($text, $selector = null)
    {
        $res = $this->proceedSee($text, $selector);
        call_user_func_array(array($this, 'assertPageContains'), $res);
    }

    protected function proceedSee($text, $selector = null)
    {
        if ($selector) {
            /** @var NodeElement[] $nodes */
            $nodes = null;
            if (Locator::isCSS($selector)) {
                $nodes = $this->session->getPage()->findAll('css', $selector);
            }
            if (!$nodes and Locator::isXPath($selector)) {
                $nodes = $this->session->getPage()->findAll('xpath', $selector);
            }
            if (empty($nodes)) {
                throw new ElementNotFound($selector, 'CSS or XPath');
            }

            $values = array();
            foreach ($nodes as $node) {
                $values[] = $node->getText();
            }
            $values = implode(" | ", $values);

            return array($text, $values, "'$selector' selector.");
        } else {
            $response = $this->session->getPage()->getText();

            return array($text, $response);
        }
    }

    public function seeLink($text, $url = null)
    {
        $text    = $this->escape($text);
        $locator = array('link', $this->session->getSelectorsHandler()->xpathLiteral($text));

        /** @var NodeElement[] $nodes */
        $nodes = $this->session->getPage()->findAll('named', $locator);

        if (!$url) {
            return $this->assertNotEmpty($nodes);
        }

        foreach ($nodes as $node) {
            if (false !== strpos($node->getAttribute('href'), $url)) {
                return $this->assertContains($text, $node->getHtml(), "with url '{$url}'");
            }
        }

        $this->fail("with url '{$url}'");
    }

    public function dontSeeLink($text, $url = null)
    {
        if (!$url) {
            $this->dontSee($text, 'a');
        }

        $text    = $this->escape($text);
        $locator = array('link', $this->session->getSelectorsHandler()->xpathLiteral($text));

        /** @var NodeElement[] $nodes */
        $nodes = $this->session->getPage()->findAll('named', $locator);

        foreach ($nodes as $node) {
            if (false !== strpos($node->getAttribute('href'), $url)) {
                $this->fail("with url '$url'");
            }
        }

        $this->assertTrue(true, "with url '$url'");
    }

    public function click($link, $context = null)
    {
        $urlBeforeClick = $this->session->getCurrentUrl();

        $el = $this->findClickable($link, $context);
        $el->click();

        if ($this->session->getCurrentUrl() != $urlBeforeClick) {
            $this->debugPageInfo();
        }
    }

    protected function debugPageInfo()
    {
        $this->debug('Moved to page ' . $this->session->getCurrentUrl());
    }

    public function seeElement($selector)
    {
        $el = $this->findEl($selector);
        $this->assertNotEmpty($el);
    }

    public function dontSeeElement($selector)
    {
        $el = array();
        try {
            $el = $this->findEl($selector);
        } catch (\PHPUnit_Framework_AssertionFailedError $e) {
            // ignore
        }
        $this->assertEmpty($el);
    }

    /**
     * @param $selector
     *
     * @return NodeElement|null
     * @throws ElementNotFound
     */
    protected function findEl($selector)
    {
        $page = $this->session->getPage();
        $el   = null;
        if (Locator::isCSS($selector)) {
            $el = $page->find('css', $selector);
        }
        if (!$el and Locator::isXPath($selector)) {
            $el = @$page->find('xpath', $selector);
        }

        if (!$el) {
            throw new ElementNotFound($selector, 'CSS or XPath');
        }

        return $el;
    }

    protected function findLinkByContent(Element $page, $link)
    {
        $literal = $this->session->getSelectorsHandler()->xpathLiteral($link);
        return $page->find('xpath', './/a[normalize-space(.)=normalize-space(' . $literal . ')]');
    }

    protected function findClickable($link, $context = null)
    {
        $page = $context
            ? $this->findEl($context)
            : $this->session->getPage();

        if (!$page) {
            $this->fail("Context element $context not found");
        }

        $el = $this->findLinkByContent($page, $link);
        if (!$el) {
            $el = $page->findLink($link);
        }
        if (!$el) {
            $el = $page->findButton($link);
        }
        if (!$el) {
            $el = $this->findEl($link);
        }

        return $el;
    }

    /**
     * Reloads current page
     */
    public function reloadPage()
    {
        $this->session->reload();
    }

    /**
     * Moves back in history
     */
    public function moveBack()
    {
        $this->session->back();
        $this->debug($this->session->getCurrentUrl());
    }

    /**
     * Moves forward in history
     */
    public function moveForward()
    {
        $this->session->forward();
        $this->debug($this->session->getCurrentUrl());
    }

    public function fillField($field, $value)
    {
        $field = $this->findField($field);
        $field->setValue($value);
    }

    public function selectOption($select, $option)
    {
        $field = $this->findField($select);
        if (is_array($option)) {
            foreach ($option as $opt) {
                $field->selectOption($opt, true);
            }
            return;
        }
        $field->selectOption($option);
    }

    public function checkOption($option)
    {
        $field = $this->findField($option);
        $field->check();
    }

    public function uncheckOption($option)
    {
        $field = $this->findField($option);
        $field->uncheck();
    }

    /**
     * @param string $selector
     *
     * @return NodeElement|null
     * @throws ElementNotFound
     */
    protected function findField($selector)
    {
        $page    = $this->session->getPage();
        $locator = array('field', $this->session->getSelectorsHandler()->xpathLiteral($selector));
        $field   = $page->find('named', $locator);

        if (!$field and Locator::isCSS($selector)) {
            $field = $page->find('css', $selector);
        }
        if (!$field and Locator::isXPath($selector)) {
            $field = @$page->find('xpath', $selector);
        }

        if (!$field) {
            throw new ElementNotFound($selector, "Field by name, label, CSS or XPath");
        }
        return $field;
    }

    public function _getCurrentUri()
    {
        $url   = $this->session->getCurrentUrl();
        $parts = parse_url($url);
        if (!$parts) {
            $this->fail("URL couldn't be parsed");
        }
        $uri = "";
        if (isset($parts['path'])) {
            $uri .= $parts['path'];
        }
        if (isset($parts['query'])) {
            $uri .= "?" . $parts['query'];
        }
        if (isset($parts['fragment'])) {
            $uri .= "#" . $parts['fragment'];
        }

        return $uri;
    }

    public function seeInCurrentUrl($uri)
    {
        $this->assertContains($uri, $this->_getCurrentUri());
    }

    public function dontSeeInCurrentUrl($uri)
    {
        $this->assertNotContains($uri, $this->_getCurrentUri());
    }

    public function seeCurrentUrlEquals($uri)
    {
        $this->assertEquals($uri, $this->_getCurrentUri());
    }

    public function dontSeeCurrentUrlEquals($uri)
    {
        $this->assertNotEquals($uri, $this->_getCurrentUri());
    }

    public function seeCurrentUrlMatches($uri)
    {
        \PHPUnit_Framework_Assert::assertRegExp($uri, $this->_getCurrentUri());
    }

    public function dontSeeCurrentUrlMatches($uri)
    {
        \PHPUnit_Framework_Assert::assertNotRegExp($uri, $this->_getCurrentUri());
    }

    public function seeCookie($cookie)
    {
        $this->assertNotNull($this->_getCookie($cookie));
    }

    public function dontSeeCookie($cookie)
    {
        $this->assertNull($this->_getCookie($cookie));
    }

    public function setCookie($cookie, $value)
    {
        $this->_setCookie($cookie, $value);
    }

    public function resetCookie($cookie)
    {
        $this->_setCookie($cookie, null);
    }

    public function grabCookie($cookie)
    {
        return $this->_getCookie($cookie);
    }

    public function grabFromCurrentUrl($uri = null)
    {
        if (!$uri) {
            return $this->session->getCurrentUrl();
        }
        $matches = array();
        $res     = preg_match($uri, $this->session->getCurrentUrl(), $matches);
        if (!$res) {
            $this->fail("Couldn't match $uri in " . $this->session->getCurrentUrl());
        }
        if (!isset($matches[1])) {
            $this->fail("Nothing to grab. A regex parameter required. Ex: '/user/(\\d+)'");
        }

        return $matches[1];
    }

    public function attachFile($field, $filename)
    {
        $field = $this->findField($field);
        $path  = \Codeception\Configuration::dataDir() . $filename;
        if (!file_exists($path)) {
            $this->fail(
                "file '$filename' not found in Codeception data path. Only files stored in data path are accepted"
            );
        }
        $field->attachFile($path);
    }

    public function seeOptionIsSelected($select, $text)
    {
        $option = $this->findSelectedOption($select);
        if (!$option) {
            $this->fail("No option is selected in $select");
        }
        $this->assertEquals($text, $option->getText());
    }

    public function dontSeeOptionIsSelected($select, $text)
    {
        $option = $this->findSelectedOption($select);
        if (!$option) {
            $this->assertNull($option);
            return;
        }
        $this->assertNotEquals($text, $option->getText());
    }

    protected function findSelectedOption($select)
    {
        $selectbox = $this->findEl($select);
        $option    = $selectbox->find('css', 'option[selected]');

        return $option;
    }

    public function seeCheckboxIsChecked($checkbox)
    {
        $node = $this->findField($checkbox);
        if (!$node) {
            $this->fail(", checkbox not found");
        }
        $this->assertTrue($node->isChecked());
    }

    public function dontSeeCheckboxIsChecked($checkbox)
    {
        $node = $this->findField($checkbox);
        if (!$node) {
            $this->fail(", checkbox not found");
        }
        $this->assertNull($node->getAttribute('checked'));
    }

    public function seeInField($field, $value)
    {
        $node = $this->findField($field);
        if (!$node) {
            $this->fail(", field not found");
        }
        $this->assertEquals(
            $value,
            $node->getTagName() == 'textarea' ? $node->getText() : $node->getAttribute('value')
        );
    }

    public function dontSeeInField($field, $value)
    {
        $node = $this->findField($field);
        if (!$node) {
            $this->fail(", field not found");
        }
        $this->assertNotEquals(
            $value,
            $node->getTagName() == 'textarea' ? $node->getText() : $node->getAttribute('value')
        );
    }

    public function grabTextFrom($cssOrXPathOrRegex)
    {
        $el = null;

        if (Locator::isCSS($cssOrXPathOrRegex)) {
            $el = $this->session->getPage()->find('css', $cssOrXPathOrRegex);
            if ($el) {
                return $el->getText();
            }
        }

        if (!$el and Locator::isXPath($cssOrXPathOrRegex)) {
            $el = @$this->session->getPage()->find('xpath', $cssOrXPathOrRegex);
            if ($el) {
                return $el->getText();
            }
        }

        if (@preg_match($cssOrXPathOrRegex, $this->session->getPage()->getContent(), $matches)) {
            return $matches[1];
        }

        throw new ElementNotFound($cssOrXPathOrRegex, 'CSS or XPath or Regex');
    }

    public function grabValueFrom($field)
    {
        $el = $this->findField($field);
        if ($el) {
            return $el->getValue();
        }
        $this->fail("Element '$field' not found");
    }

    public function seeInTitle($title)
    {
        $el = $this->session->getPage()->find('css', 'title');
        if (!$el) {
            throw new ElementNotFound('<title>', 'Tag');
        }
        $this->assertContains($title, $el->getText(), "page title contains $title");
    }

    public function dontSeeInTitle($title)
    {
        $el = $this->session->getPage()->find('css', 'title');
        if (!$el) {
            return $this->assertTrue(true);
        }
        $this->assertNotContains($title, $el->getText(), "page title contains $title");
    }

    protected function assertPageContains($needle, $haystack, $message = '')
    {
        $constraint = new Page($needle, $this->_getCurrentUri());
        $this->assertThat($haystack, $constraint, $message);
    }

    protected function assertPageNotContains($needle, $haystack, $message = '')
    {
        $constraint = new Page($needle, $this->_getCurrentUri());
        $this->assertThatItsNot($haystack, $constraint, $message);
    }

    protected function escape($string)
    {
        return (string)$string;
    }
}
