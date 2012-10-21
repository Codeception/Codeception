<?php
namespace Codeception\Util;

abstract class Mink extends \Codeception\Module
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
        } catch (\Exception $e) {
            throw new \Codeception\Exception\ModuleConfig(__CLASS__, "Provided URL can't be accessed by this driver.");
        }
    }
    
    public function _cleanup() {
        $this->session->reset();
    }

    public function _after(\Codeception\TestCase $test) {
        if ($this->session) $this->session->stop();
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

    /**
     * Checks if the document has link that contains specified
     * text (or text and url)
     *
     * @param  string $text
     * @param  string $url (Default: null)
     * @return mixed
     */
    public function seeLink($text, $url = null)
    {
        $text = $this->escape($text);

        $nodes = $this->session->getPage()->findAll(
            'named', array(
                'link', $this->session->getSelectorsHandler()->xpathLiteral($text)
            )
        );

        if (!$url) {
            return \PHPUnit_Framework_Assert::assertNotNull($nodes);
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

    /**
     * Checks if the document hasn't link that contains specified
     * text (or text and url)
     *
     * @param  string $text
     * @param  string $url (Default: null)
     * @return mixed
     */
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

    /**
     * Clicks on either link or button (for PHPBrowser) or on any selector for JS browsers.
     * Link text or css selector can be passed.
     *
     * @param $link
     */
    public function click($link) {
        $url = $this->session->getCurrentUrl();
        $el = $this->findClickable($link);
        $el->click();

        if ($this->session->getCurrentUrl() != $url) {
            $this->debug('moved to page '. $this->session->getCurrentUrl());
        }
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

        if (!$el) \PHPUnit_Framework_Assert::fail("Link | button | CSS | XPath for '$selector' not found'");
        return $el;
    }

    protected function findLinkByContent($link)
    {
        $literal = $this->session->getSelectorsHandler()->xpathLiteral($link);
        return $this->session->getPage()->find('xpath','.//a[.='.$literal.']'); // search by strict match
    }

    protected function findClickable($link)
    {
        $page = $this->session->getPage();
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

    /**
     * Fill the field with given value.
     * Field is searched by its id|name|label|value or CSS selector.
     *
     * @param $field
     * @param $value
     */
    public function fillField($field, $value)
    {
        $field = $this->findField($field);
        $field->setValue($value);
    }

    /**
     * Selects opition from selectbox.
     * Use field name|label|value|id or CSS selector to match selectbox.
     * Either values or text of options can be used to fetch option.
     *
     * @param $select
     * @param $option
     */
    public function selectOption($select, $option)
    {
        $field = $this->findField($select);
        $field->selectOption($option);
    }

    /**
     * Check matched checkbox or radiobutton.
     * Field is searched by its id|name|label|value or CSS selector.
     *
     * @param $option
     */
    public function checkOption($option)
    {
        $field = $this->findField($option);
        $field->check();
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

    /**
     * Uncheck matched checkbox or radiobutton.
     * Field is searched by its id|name|label|value or CSS selector.
     *
     * @param $option
     */
    public function uncheckOption($option)
    {
        $field = $this->findField($option);
        $field->uncheck();
    }

    /**
     * Checks if current url contains the $uri.
     *
     * @param $uri
     */
    public function seeInCurrentUrl($uri) {
        \PHPUnit_Framework_Assert::assertContains($uri, $this->session->getCurrentUrl(),'');
    }

    /**
     * Attaches file stored in Codeception data directory to field specified.
     * Field is searched by its id|name|label|value or CSS selector.
     *
     * @param $field
     * @param $filename
     */
    public function attachFile($field, $filename) {
        $field = $this->findField($field);
        $path = \Codeception\Configuration::dataDir().$filename;
        if (!file_exists($path)) \PHPUnit_Framework_Assert::fail("file $filename not found in Codeception data path. Only files stored in data path accepted");
        $field->attachFile($path);
    }

    /**
     * Asserts the checkbox is checked.
     * Field is searched by its id|name|label|value or CSS selector.
     *
     * @param $checkbox
     */
    public function seeCheckboxIsChecked($checkbox) {
       $node = $this->findField($checkbox);
        if (!$node) return \PHPUnit_Framework_Assert::fail(", checkbox not found");
        \PHPUnit_Framework_Assert::assertTrue($node->isChecked());
    }

    /**
     * Asserts that checbox is not checked
     * Field is searched by its id|name|label|value or CSS selector.
     *
     * @param $checkbox
     */
    public function dontSeeCheckboxIsChecked($checkbox) {
        $node = $this->findField($checkbox);
         if (!$node) return \PHPUnit_Framework_Assert::fail(", checkbox not found");
         \PHPUnit_Framework_Assert::assertFalse($node->isChecked());
    }

    /**
     * Checks the value of field is equal to value passed.
     *
     * @param $field
     * @param $value
     */
    public function seeInField($field, $value) {
        $node  = $this->session->getPage()->findField($field);
        if (!$node) return \PHPUnit_Framework_Assert::fail(", field not found");
        \PHPUnit_Framework_Assert::assertEquals($this->escape($value), $node->getValue());
    }

    /**
     * Checks the value in field is not equal to value passed.
     * Field is searched by its id|name|label|value or CSS selector.
     *
     * @param $field
     * @param $value
     */
    public function dontSeeInField($field, $value) {
        $node  = $this->session->getPage()->findField($field);
        if (!$node) return \PHPUnit_Framework_Assert::fail(", field not found");
        \PHPUnit_Framework_Assert::assertNotEquals($this->escape($value), $node->getValue());
    }

    /**
     * Finds and returns text contents of element.
     * Element is searched by CSS selector, XPath or matcher by regex.
     *
     * Example:
     *
     * ``` php
     * <?php
     * $heading = $I->grabTextFrom('h1');
     * $heading = $I->grabTextFrom('descendant-or-self::h1');
     * $value = $I->grabTextFrom('~<input value=(.*?)]~sgi');
     * ?>
     * ```
     *
     * @param $cssOrXPathOrRegex
     * @return mixed
     */
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


    /**
     * Finds and returns field and returns it's value.
     * Searches by field name, then by CSS, then by XPath
     *
     * Example:
     *
     * ``` php
     * <?php
     * $name = $I->grabValueFrom('Name');
     * $name = $I->grabValueFrom('input[name=username]');
     * $name = $I->grabValueFrom('descendant-or-self::form/descendant::input[@name = 'username']');
     * ?>
     * ```
     *
     * @param $field
     * @return mixed
     */
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
