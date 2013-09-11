<?php

namespace Codeception\Util;

interface WebInterface
{
    /**
     * Opens the page.
     * Requires relative uri as parameter
     *
     * Example:
     *
     * ``` php
     * <?php
     * // opens front page
     * $I->amOnPage('/');
     * // opens /register page
     * $I->amOnPage('/register');
     * ?>
     * ```
     *
     * @param $page
     */
    public function amOnPage($page);

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
     * $I->see('Sign Up','//body/h1'); // with XPath
     * ?>
     * ```
     *
     * @param $text
     * @param null $selector
     */
    public function see($text, $selector = null);

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
     * $I->dontSee('Sign Up','//body/h1'); // with XPath
     * ?>
     * ```
     *
     * @param $text
     * @param null $selector
     */
    public function dontSee($text, $selector = null);

    /**
     * Perform a click on link or button.
     * Link or button are found by their names or CSS selector.
     * Submits a form if button is a submit type.
     *
     * If link is an image it's found by alt attribute value of image.
     * If button is image button is found by it's value
     * If link or button can't be found by name they are searched by CSS selector.
     *
     * The second parameter is a context: CSS or XPath locator to narrow the search.
     *
     * Examples:
     *
     * ``` php
     * <?php
     * // simple link
     * $I->click('Logout');
     * // button of form
     * $I->click('Submit');
     * // CSS button
     * $I->click('#form input[type=submit]');
     * // XPath
     * $I->click('//form/*[@type=submit]')
     * // link in context
     * $I->click('Logout', '#nav');
     * ?>
     * ```
     * @param $link
     * @param $context
     */
    public function click($link, $context = null);

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
     * ?>
     * ```
     *
     * @param $text
     * @param null $url
     */
    public function seeLink($text, $url = null);

    /**
     * Checks if page doesn't contain the link with text specified.
     * Specify url to narrow the results.
     *
     * Examples:
     *
     * ``` php
     * <?php
     * $I->dontSeeLink('Logout'); // I suppose user is not logged in
     * ?>
     * ```
     *
     * @param $text
     * @param null $url
     */
    public function dontSeeLink($text, $url = null);

    /**
     * Checks that current uri contains a value
     *
     * ``` php
     * <?php
     * // to match: /home/dashboard
     * $I->seeInCurrentUrl('home');
     * // to match: /users/1
     * $I->seeInCurrentUrl('/users/');
     * ?>
     * ```
     *
     * @param $uri
     */
    public function seeInCurrentUrl($uri);

    /**
     * Checks that current url is equal to value.
     * Unlike `seeInCurrentUrl` performs a strict check.
     *
     * ``` php
     * <?php
     * // to match root url
     * $I->seeCurrentUrlEquals('/');
     * ?>
     * ```
     *
     * @param $uri
     */
    public function seeCurrentUrlEquals($uri);

    /**
     * Checks that current url is matches a RegEx value
     *
     * ``` php
     * <?php
     * // to match root url
     * $I->seeCurrentUrlMatches('~$/users/(\d+)~');
     * ?>
     * ```
     *
     * @param $uri
     */
    public function seeCurrentUrlMatches($uri);

    /**
     * Checks that current uri does not contain a value
     *
     * ``` php
     * <?php
     * $I->dontSeeInCurrentUrl('/users/');
     * ?>
     * ```
     *
     * @param $uri
     */
    public function dontSeeInCurrentUrl($uri);

    /**
     * Checks that current url is not equal to value.
     * Unlike `dontSeeInCurrentUrl` performs a strict check.
     *
     * ``` php
     * <?php
     * // current url is not root
     * $I->dontSeeCurrentUrlEquals('/');
     * ?>
     * ```
     *
     * @param $uri
     */
    public function dontSeeCurrentUrlEquals($uri);

    /**
     * Checks that current url does not match a RegEx value
     *
     * ``` php
     * <?php
     * // to match root url
     * $I->dontSeeCurrentUrlMatches('~$/users/(\d+)~');
     * ?>
     * ```
     *
     * @param $uri
     */
    public function dontSeeCurrentUrlMatches($uri);

    /**
     * Takes a parameters from current URI by RegEx.
     * If no url provided returns full URI.
     *
     * ``` php
     * <?php
     * $user_id = $I->grabFromCurrentUrl('~$/user/(\d+)/~');
     * $uri = $I->grabFromCurrentUrl();
     * ?>
     * ```
     *
     * @param null $uri
     * @internal param $url
     * @return mixed
     */
    public function grabFromCurrentUrl($uri = null);

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
     * $I->seeCheckboxIsChecked('//form/input[@type=checkbox and @name=agree]');
     * ?>
     * ```
     *
     * @param $checkbox
     */
    public function seeCheckboxIsChecked($checkbox);

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
     * ?>
     * ```
     *
     * @param $checkbox
     */
    public function dontSeeCheckboxIsChecked($checkbox);

    /**
     * Checks that an input field or textarea contains value.
     * Field is matched either by label or CSS or Xpath
     *
     * Example:
     *
     * ``` php
     * <?php
     * $I->seeInField('Body','Type your comment here');
     * $I->seeInField('form textarea[name=body]','Type your comment here');
     * $I->seeInField('form input[type=hidden]','hidden_value');
     * $I->seeInField('#searchform input','Search');
     * $I->seeInField('//form/*[@name=search]','Search');
     * ?>
     * ```
     *
     * @param $field
     * @param $value
     */
    public function seeInField($field, $value);

    /**
     * Checks that an input field or textarea doesn't contain value.
     * Field is matched either by label or CSS or Xpath
     * Example:
     *
     * ``` php
     * <?php
     * $I->dontSeeInField('Body','Type your comment here');
     * $I->dontSeeInField('form textarea[name=body]','Type your comment here');
     * $I->dontSeeInField('form input[type=hidden]','hidden_value');
     * $I->dontSeeInField('#searchform input','Search');
     * $I->dontSeeInField('//form/*[@name=search]','Search');
     * ?>
     * ```
     *
     * @param $field
     * @param $value
     */
    public function dontSeeInField($field, $value);

    /**
     * Selects an option in select tag or in radio button group.
     *
     * Example:
     *
     * ``` php
     * <?php
     * $I->selectOption('form select[name=account]', 'Premium');
     * $I->selectOption('form input[name=payment]', 'Monthly');
     * $I->selectOption('//form/select[@name=account]', 'Monthly');
     * ?>
     * ```
     *
     * Can select multiple options if second argument is array:
     *
     * ``` php
     * <?php
     * $I->selectOption('Which OS do you use?', array('Windows','Linux'));
     * ?>
     * ```
     *
     * @param $select
     * @param $option
     */
    public function selectOption($select, $option);

    /**
     * Ticks a checkbox.
     * For radio buttons use `selectOption` method.
     *
     * Example:
     *
     * ``` php
     * <?php
     * $I->checkOption('#agree');
     * ?>
     * ```
     *
     * @param $option
     */
    public function checkOption($option);

    /**
     * Unticks a checkbox.
     *
     * Example:
     *
     * ``` php
     * <?php
     * $I->uncheckOption('#notify');
     * ?>
     * ```
     *
     * @param $option
     */
    public function uncheckOption($option);

    /**
     * Fills a text field or textarea with value.
     * 
     * Example:
     * 
     * ``` php
     * <?php
     * $I->fillField("//input[@type='text']", "Hello World!");
     * ?>
     * ```
     *
     * @param $field
     * @param $value
     */
    public function fillField($field, $value);

    /**
     * Attaches file from Codeception data directory to upload field.
     *
     * Example:
     *
     * ``` php
     * <?php
     * // file is stored in 'tests/_data/prices.xls'
     * $I->attachFile('input[@type="file"]', 'prices.xls');
     * ?>
     * ```
     *
     * @param $field
     * @param $filename
     */
    public function attachFile($field, $filename);

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
    public function grabTextFrom($cssOrXPathOrRegex);

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
    public function grabValueFrom($field);

    /**
     * Checks if element exists on a page, matching it by CSS or XPath
     *
     * ``` php
     * <?php
     * $I->seeElement('.error');
     * $I->seeElement('//form/input[1]');
     * ?>
     * ```
     * @param $selector
     */
    public function seeElement($selector);

    /**
     * Checks if element does not exist (or is visible) on a page, matching it by CSS or XPath
     *
     * Example:
     * 
     * ``` php
     * <?php
     * $I->dontSeeElement('.error');
     * $I->dontSeeElement('//form/input[1]');
     * ?>
     * ```
     * @param $selector
     */
    public function dontSeeElement($selector);

    /**
     * Checks if option is selected in select field.
     *
     * ``` php
     * <?php
     * $I->seeOptionIsSelected('#form input[name=payment]', 'Visa');
     * ?>
     * ```
     *
     * @param $selector
     * @param $optionText
     * @return mixed
     */
    public function seeOptionIsSelected($selector, $optionText);

    /**
     * Checks if option is not selected in select field.
     *
     * ``` php
     * <?php
     * $I->dontSeeOptionIsSelected('#form input[name=payment]', 'Visa');
     * ?>
     * ```
     *
     * @param $selector
     * @param $optionText
     * @return mixed
     */
    public function dontSeeOptionIsSelected($selector, $optionText);

    /**
     * Checks that page title contains text.
     *
     * ``` php
     * <?php
     * $I->seeInTitle('Blog - Post #1');
     * ?>
     * ```
     *
     * @param $title
     * @return mixed
     */
    public function seeInTitle($title);

    /**
     * Checks that page title does not contain text.
     *
     * @param $title
     * @return mixed
     */
    public function dontSeeInTitle($title);

}
