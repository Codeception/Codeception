<?php
namespace Codeception\Util;

abstract class Mink extends \Codeception\Module
{
    /**
     * @var \Behat\Mink\Session
     */
    public $session;

    public function _before(\Codeception\TestCase $test) {
        // should be done to have request and response not empty
        $this->session->visit($this->config['url'].'/');
    }

    public function _after(\Codeception\TestCase $test) {
        $this->session->stop();
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
            $nodes = $this->session->getPage()->findAll('css', $selector);
		    $values = '';
		    foreach ($nodes as $node) {
		        $values .= '<!-- Merged Output -->'.$node->getText();
		    }
			return array('contains', $this->escape($text), $values, "'$selector' selector. For more details look for page snapshot in the log directory");
        }

        $response = $this->session->getPage()->getContent();

        if (strpos($response, '<!DOCTYPE')!==false) {
            $response = array();
            $title = $this->session->getPage()->find('css','title');
            if ($title) $response['title'] = trim($title->getText());

            $h1 = $this->session->getPage()->find('css','h1');
            if ($h1 && is_object($title)) $response['h1'] = trim($h1->getText());

            $response['uri'] = $this->session->getCurrentUrl();
            if ($this->session->getStatusCode()) $response['responseCode'] = $this->session->getStatusCode();
            $response = json_encode($response);
            $response = 'html page response '.$response;
        }

        return array('contains', $text, strip_tags($response), "'$text' in ".$response.'. For more details look for page snapshot in the log directory');
    }


    public function seeLink($text, $url = null)
    {
        $text = $this->escape($text);
        $nodes = $this->session->getPage()->findLink($text);
        if (!$url) return \PHPUnit_Framework_Assert::assertNotEmpty($nodes);
        foreach ($nodes as $node) {
            if (false !== strpos($node->getAttribute('href'), $url)) {
                return \PHPUnit_Framework_Assert::assertContains($text, $node->getHtml(), "with url '$url'");
            }
        }
        \PHPUnit_Framework_Assert::fail("with url '$url'");
    }


    public function dontSeeLink($text, $url = null)
    {
        if (!$url) return $this->dontSee($text, 'a');
        $text = $this->escape($text);
        $nodes = $this->session->getPage()->findAll('named', $text);
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
        $el = $this->findEl($link);
        $el->click();

        if ($this->session->getCurrentUrl() != $url) {
            $this->debug('moved to page '. $this->session->getCurrentUrl());
        }
    }

    /**
     * @param $link
     * @return \Behat\Mink\Element\NodeElement
     */
    protected function findEl($link)
    {
        $link = $this->escape($link);
        $page = $this->session->getPage();
        $el = $page->findLink($link);
        if (!$el) $el = $page->findButton($link);
        if (!$el) $el = $page->find('css', $link);
        if (!$el) \PHPUnit_Framework_Assert::fail("Link or Button or CSS for '$link' not found'");
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
        $selector = $this->escape($selector);
        $page = $this->session->getPage();
        $field = $page->find('named', array(
            'field', $this->session->getSelectorsHandler()->xpathLiteral($selector)
        ));
        if (!$field) $field = $page->find('css', $selector);
        if (!$field) \PHPUnit_Framework_Assert::fail("Field matching id|name|label|value or css selector does not exists");
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

    protected function escape($string)
    {
        return addslashes($string);

    }

}
