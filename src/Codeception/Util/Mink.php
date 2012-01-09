<?php
namespace Codeception\Util;

abstract class Mink extends \Codeception\Module implements FrameworkInterface
{
    /**
     * @var \Behat\Mink\Session
     */
    public $session;

    public function _before(\Codeception\TestCase $test) {
        // should be done to have request and response not empty
        $this->session->visit($this->config['url']);
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

    /**
     * Check if current page doesn't contain the text specified.
     * Specify the css selector to match only specific region.
     *
     * Examples:
     *
     * ```php
     * <?php
     * $I->dontSee('Login'); // I can suppose user is already logged in
     * $I->dontSee('Sign Up','h1'); // I can suppose it's not a signup page
     *
     * ```
     *
     * @param $text
     * @param null $selector
     */
    public function dontSee($text, $selector = null) {
        $res = $this->proceedSee($text, $selector);
        $this->assertNot($res);
    }

    /**
     * Check if current page contains the text specified.
     * Specify the css selector to match only specific region.
     *
     * Examples:
     *
     * ``` php
     * <?php
     * $I->see('Logout'); // I can suppose user is logged in
     * $I->see('Sign Up','h1'); // I can suppose it's a signup page
     *
     * ```
     *
     * @param $text
     * @param null $selector
     */
    public function see($text, $selector = null) {
        $res = $this->proceedSee($text, $selector);
        $this->assert($res);
    }

    protected function proceedSee($text, $selector = null) {
        if ($selector) {
            $nodes = $this->session->getPage()->findAll('css', $selector);
		    $values = array();
		    foreach ($nodes as $node) {
		        $values[] = trim($node->getText());
		    }
			return array('contains', $text, implode('<!-- Merged Output -->',$values), "'$selector' selector. For more details look for page snapshot in the log directory");
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
        return array('contains', $text, strip_tags($this->session->getPage()->getContent()), "'$text' in ".$response.'. For more details look for page snapshot in the log directory');
    }

    /**
     * Checks if there is a link with text specified.
     * Specify url to match link with exact this url.
     *
     * Examples:
     *
     * ``` php
     * <?php
     * $I->seeLink('Logout'); // matches <a href="#">Logout</a>
     * $I->seeLink('Logout','/logout'); // matches <a href="/logout">Logout</a>
     *
     * ```
     *
     * @param $text
     * @param null $url
     */
    public function seeLink($text, $url = null)
    {
        if (!$url) return $this->see($text, 'a');
        $nodes = $nodes = $this->session->getPage()->findAll('named', $text);
        foreach ($nodes as $node) {
            if (false !== strpos($node->getAttribute('href'), $url)) {
                return \PHPUnit_Framework_Assert::assertContains($text, $node->getHtml(), "with url '$url'");
            }
        }
        \PHPUnit_Framework_Assert::fail("with url '$url'");
    }

    /**
     * Checks if page doesn't contain the link with text specified.
     * Specify url to narrow the results.
     *
     * Examples:
     *
     * ``` php
     * <?php
     * $I->dontSeeLink('Logout'); // I suppose user is not logged in
     *
     * ```
     *
     * @param $text
     * @param null $url
     */
    public function dontSeeLink($text, $url = null)
    {
        if (!$url) return $this->dontSee($text, 'a');
        $nodes = $nodes = $this->session->getPage()->findAll('named', $text);
        foreach ($nodes as $node) {
            if (false !== strpos($node->getAttribute('href'), $url)) {
                return \PHPUnit_Framework_Assert::fail("with url '$url'");
            }
        }
        return \PHPUnit_Framework_Assert::assertTrue(true, "with url '$url'");
    }

    /**
     * Clicks on either link (for PHPBrowser) or on any selector for JS browsers.
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
     * @return \Behat\Mink\Element\Behat\Mink\Element\NodeElement
     */
    protected function findEl($link)
    {
        $page = $this->session->getPage();
        $el = $page->findLink($link);
        if (!$el) $el = $page->find('css', $link);
        if (!$el) \PHPUnit_Framework_Assert::fail("Element for '$link' not found'");
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
     * Fill the field found by it's name with given value
     *
     * @param $field
     * @param $value
     */
    public function fillField($field, $value)
    {
        $this->session->getPage()->fillField($field, $value);
    }

    /**
     * Shortcut for filling multiple fields by their names.
     * Array with field names => values expected.
     *
     *
     * @param array $fields
     */
    public function fillFields(array $fields)
    {
        foreach ($fields as $field => $value) {
            $this->fillField($field, $value);
        }
    }

    /**
     * Press the button, found by it's name.
     *
     * @param $button
     */
    public function press($button) {
        $this->session->getPage()->pressButton($button);
    }

    /**
     * Selects opition from selectbox.
     * Use CSS selector to match selectbox.
     * Either values or text of options can be used to fetch option.
     *
     * @param $select
     * @param $option
     */
    public function selectOption($select, $option)
    {
        $this->session->getPage()->selectFieldOption($select, $option);
    }

    /**
     * Check matched checkbox or radiobutton.
     * @param $option
     */
    public function checkOption($option)
    {
        $this->session->getPage()->checkField($option);
    }

    /**
     * Uncheck matched checkbox or radiobutton.
     * @param $option
     */
    public function uncheckOption($option)
    {
        $this->session->getPage()->uncheckField($option);
    }

    public function attachFileToField($field, $path)
    {
        $this->session->getPage()->attachFileToField($field, $path);
    }

    /**
     * Checks if current url contains the $uri.
     * @param $uri
     */
    public function seeInCurrentUrl($uri) {
        \PHPUnit_Framework_Assert::assertContains($uri, $this->session->getCurrentUrl(),'');
    }

    /**
     * Assert if the specified checkbox is checked.
     * Use css selector or xpath to match.
     *
     * Example:
     *
     * ``` php
     * <?php
     * $I->seeCheckboxIsChecked('#agree'); // I suppose user agreed to terms
     * $I->seeCheckboxIsChecked('#signup_form input[type=checkbox]'); // I suppose user agreed to terms, If there is only one checkbox in form.
     *
     * ```
     *
     * @param $selector
     */
    public function seeCheckboxIsChecked($checkbox) {
       $node = $this->session->getPage()->findField($checkbox);
        if (!$node) return \PHPUnit_Framework_Assert::fail(", checkbox not found");
        \PHPUnit_Framework_Assert::assertTrue($node->isChecked);
    }

    /**
     * Assert if the specified checkbox is unchecked.
     * Use css selector or xpath to match.
     *
     * Example:
     *
     * ``` php
     * <?php
     * $I->dontSeeCheckboxIsChecked('#agree'); // I suppose user didn't agree to terms
     * $I->seeCheckboxIsChecked('#signup_form input[type=checkbox]'); // I suppose user didn't check the first checkbox in form.
     *
     * ```
     *
     * @param $selector
     */
    public function dontSeeCheckboxIsChecked($checkbox) {
        $node = $this->session->getPage()->findField($checkbox);
         if (!$node) return \PHPUnit_Framework_Assert::fail(", checkbox not found");
         \PHPUnit_Framework_Assert::assertFalse($node->isChecked);
    }

    /**
     * Checks matched field has a passed value
     *
     * @param $field
     * @param $value
     */
    public function seeInField($field, $value) {
        $node  = $this->session->getPage()->findField($field);
        if (!$node) return \PHPUnit_Framework_Assert::fail(", field not found");
        \PHPUnit_Framework_Assert::assertEquals($value, $node->getValue());
    }

    /**
     * Checks matched field doesn't contain a value passed
     *
     * @param $field
     * @param $value
     */
    public function dontSeeInField($field, $value) {
        $node  = $this->session->getPage()->findField($field);
        if (!$node) return \PHPUnit_Framework_Assert::fail(", field not found");
        \PHPUnit_Framework_Assert::assertNotEquals($value, $node->getValue());

    }

}
