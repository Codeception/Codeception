<?php
namespace Codeception\Util;

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
            $this->session->visit($this->config['url'].'/');
            $this->session->stop();
        } catch (\Exception $e) {
            throw new \Codeception\Exception\ModuleConfig(__CLASS__, "Provided URL can't be accessed by this driver." . $e->getMessage());
        }
    }
    
    public function _before(\Codeception\TestCase $test)
    {
        if ($this->session) $this->session->start();
    }

    public function _after(\Codeception\TestCase $test) {
        if ($this->session) $this->session->stop();
    }

    public function _getUrl()
    {
        if (!isset($this->config['url']))
            throw new \Codeception\Exception\ModuleConfig(__CLASS__, "Module connection failure. The URL for client can't bre retrieved");
        return $this->config['url'];
    }

    public function _setHeader($header, $value)
    {
        $this->session->setRequestHeader($header, $value);
    }

    public function _setCookie($cookie, $value)
    {
        $this->session->setCookie($cookie, $value);
    }

    public function _getResponseHeader($header)
    {
        $headers = $this->session->getResponseHeaders();
        if (!isset($headers[$header])) return false;
        return $headers[$header];
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
        $this->session->visit($this->config['url'].$page);
    }


    public function dontSee($text, $selector = null) {
        $res = $this->proceedSee($text, $selector);
        $this->assertNot($res);
    }


    public function see($text, $selector = null) {
        $res = $this->proceedSee($text, $selector);
        $this->assert($res);
    }

    protected function proceedSee($text, $selector = null) {
        if ($selector) {
            try {
                $nodes = $this->session->getPage()->findAll('css', $selector);
            } catch (\Symfony\Component\CssSelector\Exception\ParseException $e) {
                $nodes = $this->session->getPage()->findAll('xpath', $selector);
            }
		    $values = '';
		    foreach ($nodes as $node) {
		        $values .= '<!-- Merged Output -->'.$node->getText();
		    }
			return array('pageContains', $this->escape($text), $values, "'$selector' selector.");
        }

        $response = $this->session->getPage()->getText();

        $output = Framework::formatResponse($response);

        return array('pageContains', $this->escape($text), $response, "'$text' in ".$output.'.');
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

        if ($this->session->getCurrentUrl() != $url) {
            $this->debug('moved to page '. $this->session->getCurrentUrl());
        }
    }

    public function seeElement($selector)
    {
        $el = $this->findEl($selector);
        $this->assertNotEmpty($el);
    }

    /**
     * @param $selector
     * @return \Behat\Mink\Element\NodeElement
     */
    protected function findEl($selector)
    {
        $page = $this->session->getPage();
        $el = null;
        try {
            \Symfony\Component\CssSelector\CssSelector::toXPath($selector);
            $el = $page->find('css', $selector);
        } catch (\Symfony\Component\CssSelector\Exception\ParseException $e) {}

        if (!$el) $el = @$page->find('xpath',$selector);

        if (!$el) \PHPUnit_Framework_Assert::fail("CSS or XPath for '$selector' not found'");
        return $el;
    }

    protected function findLinkByContent($link)
    {
        $literal = $this->session->getSelectorsHandler()->xpathLiteral($link);
        return $this->session->getPage()->find('xpath','.//a[.='.$literal.']'); // search by strict match
    }

    protected function findClickable($link, $context = null)
    {
        $page = $context
            ? $this->findEl($context)
            : $this->session->getPage();

        if (!$page) {
            $this->fail("Context element $context not found");
        }
        $el = $this->findLinkByContent($link);
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
     * @return \Behat\Mink\Element\NodeElement
     */
    protected function findField($selector)
    {
        $page = $this->session->getPage();
        $field = $page->find('named', array(
            'field', $this->session->getSelectorsHandler()->xpathLiteral($selector)
        ));

        try {
            if (!$field) $field = $page->find('css', $selector);
        } catch (\Symfony\Component\CssSelector\Exception\ParseException $e) {}

        if (!$field) $field = @$page->find('xpath', $selector);

        if (!$field) \PHPUnit_Framework_Assert::fail("Field matching id|name|label|value or css or xpath selector does not exist");
        return $field;
    }



    public function seeInCurrentUrl($uri) {
        \PHPUnit_Framework_Assert::assertContains($uri, $this->session->getCurrentUrl(),'');
    }

    public function attachFile($field, $filename) {
        $field = $this->findField($field);
        $path = \Codeception\Configuration::dataDir().$filename;
        if (!file_exists($path)) \PHPUnit_Framework_Assert::fail("file $filename not found in Codeception data path. Only files stored in data path accepted");
        $field->attachFile($path);
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
        try {
            $el = $this->session->getPage()->find('css', $cssOrXPathOrRegex);
        } catch (\Symfony\Component\CssSelector\Exception\ParseException $e) {}

        if ($el) return $el->getText();

        if (!$el) $el = @$this->session->getPage()->find('xpath', $cssOrXPathOrRegex);

        if ($el) return $el->getText();

        if (@preg_match($cssOrXPathOrRegex, $this->session->getPage()->getContent(), $matches)) {
            return $matches[1];
        }
        $this->fail("Element that matches '$cssOrXPathOrRegex' not found");
    }


    public function grabValueFrom($field) {
        $el = $this->findField($field);
        if ($el) {
            return $el->getValue();
        }
        $this->fail("Element '$field' not found");
    }

    public function grabAttribute() {

    }

    protected function escape($string)
    {
        return $string;
    }


}
