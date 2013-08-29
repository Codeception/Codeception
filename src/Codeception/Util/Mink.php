<?php
namespace Codeception\Util;

use Codeception\Exception\ElementNotFound;
use Symfony\Component\CssSelector\CssSelector;
use Symfony\Component\CssSelector\Exception\ParseException;
use Symfony\Component\CssSelector\XPathExpr;

abstract class Mink extends \Codeception\Module implements RemoteInterface, WebInterface
{
    /**
     * @var \Behat\Mink\Session
     */
    public $session = null;


    public function _initialize() {
        if (!$this->session) throw new \Codeception\Exception\Module(__CLASS__, "Module is not initialized. Mink session is not started in _initialize method of module.");;
        try {
            $this->session->start();
            $this->session->visit(rtrim($this->config['url'], '/'));
            $this->session->stop();
        } catch (\Exception $e) {
            throw new \Codeception\Exception\ModuleConfig(__CLASS__, "Provided URL can't be accessed by this driver." . $e->getMessage());
        }
    }

    public function _before(\Codeception\TestCase $test)
    {
        $this->session->start();
    }

    public function _after(\Codeception\TestCase $test) {
        $this->session->stop();
    }

    public function _getUrl()
    {
        if (!isset($this->config['url']))
            throw new \Codeception\Exception\ModuleConfig(__CLASS__, "Module connection failure. The URL for client can't bre retrieved");
        return $this->config['url'];
    }

    public function _setCookie($cookie, $value)
    {
        $this->session->setCookie($cookie, $value);
    }

    public function _getCookie($cookie)
    {
    	return $this->session->getCookie($cookie);
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
//        $this->debug($host . $page);
        $this->session->visit($host .'/' .$page);
    }

    public function amOnSubdomain($subdomain)
    {
        $url = $this->config['url'];
        $url = preg_replace('~(https?:\/\/)(.*\.)(.*\.)~', "$1$3", $url); // removing current subdomain
        $url = preg_replace('~(https?:\/\/)(.*)~', "$1$subdomain.$2", $url); // inserting new
        $this->_reconfigure(array('url' => $url));
    }


    public function dontSee($text, $selector = null) {
        try {
            $res = $this->proceedSee($text, $selector);
        } catch (ElementNotFound $e) {
            $this->assertFalse(false, "$selector not found on page");
            return;
        }
        call_user_func_array(array($this, 'assertPageNotContains'), $res);
    }


    public function see($text, $selector = null) {
        $res = $this->proceedSee($text, $selector);
        call_user_func_array(array($this, 'assertPageContains'), $res);
    }

    protected function proceedSee($text, $selector = null) {
        if ($selector) {
            $nodes = null;

            if (Locator::isCSS($selector)) {
                $nodes = $this->session->getPage()->findAll('css', $selector);
            }

            if (!$nodes and Locator::isXPath($selector)) {
                $nodes = $this->session->getPage()->findAll('xpath', $selector);
            }
            if (empty($nodes)) throw new ElementNotFound($selector, 'CSS or XPath');

		    $values = array();

		    foreach ($nodes as $node) {
		        $values [] = $node->getText();
		    }
            $values = implode(" | ",$values);
			return array($text, $values, "'$selector' selector.");
        }

        $response = $this->session->getPage()->getText();

        return array($text, $response);
    }


    public function seeLink($text, $url = null)
    {
        $text = $this->escape($text);

        $nodes = $this->session->getPage()->findAll(
            'named', array(
                'link', $this->session->getSelectorsHandler()->xpathLiteral($text)
            )
        );

        if (!$url) {
            return \PHPUnit_Framework_Assert::assertNotEmpty($nodes);
        }

        foreach ($nodes as $node) {
            if (false !== strpos($node->getAttribute('href'), $url)) {
                return \PHPUnit_Framework_Assert::assertContains(
                    $text, $node->getHtml(), "with url '{$url}'"
                );
            }
        }

        return \PHPUnit_Framework_Assert::fail("with url '{$url}'");
    }


    public function dontSeeLink($text, $url = null)
    {
        if (!$url) {
            return $this->dontSee($text, 'a');
        }

        $text = $this->escape($text);

        $nodes = $this->session->getPage()->findAll(
            'named', array(
                'link', $this->session->getSelectorsHandler()->xpathLiteral($text)
            )
        );

        foreach ($nodes as $node) {
            if (false !== strpos($node->getAttribute('href'), $url)) {
                return \PHPUnit_Framework_Assert::fail("with url '$url'");
            }
        }

        return \PHPUnit_Framework_Assert::assertTrue(true, "with url '$url'");
    }


    public function click($link, $context = null) {
        $url = $this->session->getCurrentUrl();
        $el = $this->findClickable($link, $context);
        $el->click();

        if ($this->session->getCurrentUrl() != $url) $this->debugPageInfo();
    }

    protected function debugPageInfo()
    {
        $this->debug('Moved to page '. $this->session->getCurrentUrl());
    }

    public function seeElement($selector)
    {
        $el = $this->findEl($selector);
        $this->assertNotEmpty($el);
    }

    public function dontSeeElement($selector)
    {
        $el = array();
        try{
            $el = $this->findEl($selector);
        } catch (\PHPUnit_Framework_AssertionFailedError $e) {
        }
        $this->assertEmpty($el);
    }

    /**
     * @param $selector
     * @return \Behat\Mink\Element\NodeElement|null
     * @throws \Codeception\Exception\ElementNotFound
     */
    protected function findEl($selector)
    {
        $page = $this->session->getPage();
        $el = null;
        if (Locator::isCSS($selector)) {
            $el = $page->find('css', $selector);
        }
        if (!$el and Locator::isXPath($selector)) {
            $el = @$page->find('xpath',$selector);
        }

        if (!$el) throw new ElementNotFound($selector, 'CSS or XPath');
        return $el;
    }

    protected function findLinkByContent($page, $link)
    {
        $literal = $this->session->getSelectorsHandler()->xpathLiteral($link);
        return $page->find('xpath','.//a[normalize-space(.)=normalize-space('.$literal.')]');
    }

    protected function findClickable($link, $context = null)
    {
        $page = $context
            ? $this->findEl($context)
            : $this->session->getPage();

        if (!$page) $this->fail("Context element $context not found");

        $el = $this->findLinkByContent($page, $link);
        if (!$el) $el = $page->findLink($link);
        if (!$el) $el = $page->findButton($link);
        if (!$el) $el = $this->findEl($link);
        return $el;
    }

    /**
     * Reloads current page
     */
    public function reloadPage() {
        $this->session->reload();
    }

    /**
     * Moves back in history
     */
    public function moveBack() {
        $this->session->back();
        $this->debug($this->session->getCurrentUrl());
    }

    /**
     * Moves forward in history
     */
    public function moveForward() {
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
     * @param $selector
     * @return \Behat\Mink\Element\NodeElement|null
     * @throws \Codeception\Exception\ElementNotFound
     */
    protected function findField($selector)
    {
        $page = $this->session->getPage();
        $field = $page->find('named', array(
            'field', $this->session->getSelectorsHandler()->xpathLiteral($selector)
        ));

        if (!$field and Locator::isCSS($selector)) $field = $page->find('css', $selector);
        if (!$field and Locator::isXPath($selector)) $field = @$page->find('xpath', $selector);

        if (!$field) throw new ElementNotFound($selector, "Field by name, label, CSS or XPath");
        return $field;
    }


    public function _getCurrentUri()
    {
        $url = $this->session->getCurrentUrl();
        $parts = parse_url($url);
        if (!$parts) $this->fail("URL couldn't be parsed");
        $uri = "";
        if (isset($parts['path'])) $uri .= $parts['path'];
        if (isset($parts['query'])) $uri .= "?".$parts['query'];
        if (isset($parts['fragment'])) $uri .= "#".$parts['fragment'];
        return $uri;
    }

    public function seeInCurrentUrl($uri) {
        \PHPUnit_Framework_Assert::assertContains($uri, $this->_getCurrentUri());
    }

    public function dontSeeInCurrentUrl($uri)
    {
        \PHPUnit_Framework_Assert::assertNotContains($uri, $this->_getCurrentUri());
    }

    public function seeCurrentUrlEquals($uri)
    {
        \PHPUnit_Framework_Assert::assertEquals($uri, $this->_getCurrentUri());
    }

    public function dontSeeCurrentUrlEquals($uri)
    {
        \PHPUnit_Framework_Assert::assertNotEquals($uri, $this->_getCurrentUri());
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
    	\PHPUnit_Framework_Assert::assertNotNull($this->_getCookie($cookie));
    }

    public function dontSeeCookie($cookie)
    {
    	\PHPUnit_Framework_Assert::assertNull($this->_getCookie($cookie));
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
        if (!$uri) return $this->session->getCurrentUrl();
        $matches = array();
        $res = preg_match($uri, $this->session->getCurrentUrl(), $matches);
        if (!$res) $this->fail("Couldn't match $uri in ".$this->session->getCurrentUrl());
        if (!isset($matches[1])) $this->fail("Nothing to grab. A regex parameter required. Ex: '/user/(\\d+)'");
        return $matches[1];
    }

    public function attachFile($field, $filename) {
        $field = $this->findField($field);
        $path = \Codeception\Configuration::dataDir().$filename;
        $dataDirIsLocal = \Codeception\Configuration::dataDirIsLocal();

        // If the file is local, check to see if it exists
        if ($dataDirIsLocal) {
            if (!file_exists($path)) \PHPUnit_Framework_Assert::fail("file $filename not found in Codeception data path. Only files stored in data path accepted");
            $field->attachFile($path);
        } else {
            // File is not local, we cannot check to see if the file exists
            $field->attachFile($path);
        }
    }

    public function seeOptionIsSelected($select, $text)
    {
        $option = $this->findSelectedOption($select);
        if (!$option) $this->fail("No option is selected in $select");
        $this->assertEquals($text, $option->getText());
    }

    public function dontSeeOptionIsSelected($select, $text)
    {
        $option = $this->findSelectedOption($select);
        if (!$option) {
            \PHPUnit_Framework_Assert::assertNull($option);
            return;
        }
        $this->assertNotEquals($text, $option->getText());
    }

    protected function findSelectedOption($select)
    {
        $selectbox = $this->findEl($select);
        $option = $selectbox->find('css','option[selected]');
        return $option;
    }

    public function seeCheckboxIsChecked($checkbox) {
        $node = $this->findField($checkbox);
        if (!$node) return \PHPUnit_Framework_Assert::fail(", checkbox not found");
        \PHPUnit_Framework_Assert::assertTrue($node->isChecked());
    }

    public function dontSeeCheckboxIsChecked($checkbox) {
        $node = $this->findField($checkbox);
         if (!$node) return \PHPUnit_Framework_Assert::fail(", checkbox not found");
         \PHPUnit_Framework_Assert::assertFalse($node->isChecked());
    }

    public function seeInField($field, $value) {
        $node  = $this->findField($field);
        if (!$node) return \PHPUnit_Framework_Assert::fail(", field not found");
        $this->assertEquals($value, $node->getTagName() == 'textarea' ? $node->getText() : $node->getAttribute('value'));
    }


    public function dontSeeInField($field, $value) {
        $node  = $this->findField($field);
        if (!$node) return \PHPUnit_Framework_Assert::fail(", field not found");
        $this->assertNotEquals($value, $node->getTagName() == 'textarea' ? $node->getText() : $node->getAttribute('value'));
    }

    public function grabTextFrom($cssOrXPathOrRegex) {
        $el = null;

        if (Locator::isCSS($cssOrXPathOrRegex)) {
            $el = $this->session->getPage()->find('css', $cssOrXPathOrRegex);
            if ($el) return $el->getText();
        }

        if (!$el and Locator::isXPath($cssOrXPathOrRegex)) {
            $el = @$this->session->getPage()->find('xpath', $cssOrXPathOrRegex);
            if ($el) return $el->getText();
        }

        if (@preg_match($cssOrXPathOrRegex, $this->session->getPage()->getContent(), $matches)) {
            return $matches[1];
        }

        throw new ElementNotFound($cssOrXPathOrRegex, 'CSS or XPath or Regex');
    }


    public function grabValueFrom($field) {
        $el = $this->findField($field);
        if ($el) {
            return $el->getValue();
        }
        $this->fail("Element '$field' not found");
    }

    public function seeInTitle($title)
    {
        $el = $this->session->getPage()->find('css', 'title');
        if (!$el) throw new ElementNotFound("<title>","Tag");
        $this->assertContains($title, $el->getText(), "page title contains $title");
    }

    public function dontSeeInTitle($title)
    {
        $el = $this->session->getPage()->find('css', 'title');
        if (!$el) return $this->assertTrue(true);
        $this->assertNotContains($title, $el->getText(), "page title contains $title");
    }

    protected function assertPageContains($needle, $haystack, $message = '')
    {
        $constraint = new \Codeception\PHPUnit\Constraint\Page($needle, $this->_getCurrentUri());
        $this->assertThat($haystack, $constraint, $message);
    }

    protected function assertPageNotContains($needle, $haystack, $message = '')
    {
        $constraint = new \Codeception\PHPUnit\Constraint\Page($needle, $this->_getCurrentUri());
        $this->assertThatItsNot($haystack, $constraint,$message);
    }

    protected function escape($string)
    {
        return (string)$string;
    }




}
