<?php
// This class was automatically generated by build task
// You can change it manually, but it will be overwritten on next build
// @codingStandardsIgnoreFile

use Codeception\Maybe;
use Codeception\Module\PhpBrowser;
use Codeception\Module\Filesystem;
use Codeception\Module\OtherHelper;

/**
 * Inherited methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void amTesting($method)
 * @method void amTestingMethod($method)
 * @method void testMethod($signature)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($role)
*/

class AbsolutelyOtherGuy extends \Codeception\AbstractGuy
{
    
    /**
     * Submits a form located on page.
     * Specify the form by it's css or xpath selector.
     * Fill the form fields values as array.
     *
     * Skipped fields will be filled by their values from page.
     * You don't need to click the 'Submit' button afterwards.
     * This command itself triggers the request to form's action.
     *
     * Examples:
     *
     * ``` php
     * <?php
     * $I->submitForm('#login', array('login' => 'davert', 'password' => '123456'));
     *
     * ```
     *
     * For sample Sign Up form:
     *
     * ``` html
     * <form action="/sign_up">
     *     Login: <input type="text" name="user[login]" /><br/>
     *     Password: <input type="password" name="user[password]" /><br/>
     *     Do you agree to out terms? <input type="checkbox" name="user[agree]" /><br/>
     *     Select pricing plan <select name="plan"><option value="1">Free</option><option value="2" selected="selected">Paid</option></select>
     *     <input type="submit" value="Submit" />
     * </form>
     * ```
     * I can write this:
     *
     * ``` php
     * <?php
     * $I->submitForm('#userForm', array('user' => array('login' => 'Davert', 'password' => '123456', 'agree' => true)));
     *
     * ```
     * Note, that pricing plan will be set to Paid, as it's selected on page.
     *
     * @param $selector
     * @param $params
     * @see PhpBrowser::submitForm()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function submitForm($selector, $params) {
        $this->scenario->action('submitForm', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
    }

 
    /**
     * If your page triggers an ajax request, you can perform it manually.
     * This action sends a POST ajax request with specified params.
     * Additional params can be passed as array.
     *
     * Example:
     *
     * Imagine that by clicking checkbox you trigger ajax request which updates user settings.
     * We emulate that click by running this ajax request manually.
     *
     * ``` php
     * <?php
     * $I->sendAjaxPostRequest('/updateSettings', array('notifications' => true); // POST
     * $I->sendAjaxGetRequest('/updateSettings', array('notifications' => true); // GET
     *
     * ```
     *
     * @param $uri
     * @param $params
     * @see PhpBrowser::sendAjaxPostRequest()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function sendAjaxPostRequest($uri, $params = null) {
        $this->scenario->action('sendAjaxPostRequest', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
    }

 
    /**
     * If your page triggers an ajax request, you can perform it manually.
     * This action sends a GET ajax request with specified params.
     *
     * See ->sendAjaxPostRequest for examples.
     *
     * @param $uri
     * @param $params
     * @see PhpBrowser::sendAjaxGetRequest()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function sendAjaxGetRequest($uri, $params = null) {
        $this->scenario->action('sendAjaxGetRequest', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
    }

 
    /**
     * Opens the page.
     *
     * @param $page
     * @see PhpBrowser::amOnPage()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function amOnPage($page) {
        $this->scenario->condition('amOnPage', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
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
     * $I->dontSee('Sign Up','//body/h1'); // with XPath
     * ```
     *
     * @param $text
     * @param null $selector
     * @see PhpBrowser::dontSee()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function dontSee($text, $selector = null) {
        $this->scenario->action('dontSee', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
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
     * $I->see('Sign Up','//body/h1'); // with XPath
     *
     * ```
     *
     * @param $text
     * @param null $selector
     * @see PhpBrowser::see()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function see($text, $selector = null) {
        $this->scenario->assertion('see', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
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
     * @see PhpBrowser::seeLink()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function seeLink($text, $url = null) {
        $this->scenario->assertion('seeLink', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
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
     * @see PhpBrowser::dontSeeLink()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function dontSeeLink($text, $url = null) {
        $this->scenario->action('dontSeeLink', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
    }

 
    /**
     * Perform a click on link or button.
     * Link or button are found by their names or CSS selector.
     * Submits a form if button is a submit type.
     *
     * If link is an image it's found by alt attribute value of image.
     * If button is image button is found by it's value
     * If link or button can't be found by name they are searched by CSS selector.
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
     * ?>
     * ```
     * @param $link
     * @see PhpBrowser::click()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function click($link) {
        $this->scenario->action('click', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
    }

 
    /**
     * Reloads current page
     * @see PhpBrowser::reloadPage()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function reloadPage() {
        $this->scenario->action('reloadPage', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
    }

 
    /**
     * Moves back in history
     * @see PhpBrowser::moveBack()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function moveBack() {
        $this->scenario->action('moveBack', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
    }

 
    /**
     * Moves forward in history
     * @see PhpBrowser::moveForward()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function moveForward() {
        $this->scenario->action('moveForward', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
    }

 
    /**
     * Fills a text field or textarea with value.
     *
     * @param $field
     * @param $value
     * @see PhpBrowser::fillField()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function fillField($field, $value) {
        $this->scenario->action('fillField', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
    }

 
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
     * @param $select
     * @param $option
     * @see PhpBrowser::selectOption()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function selectOption($select, $option) {
        $this->scenario->action('selectOption', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
    }

 
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
     * @see PhpBrowser::checkOption()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function checkOption($option) {
        $this->scenario->action('checkOption', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
    }

 
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
     * @see PhpBrowser::uncheckOption()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function uncheckOption($option) {
        $this->scenario->action('uncheckOption', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
    }

 
    /**
     * Checks that current uri contains value
     *
     * @param $uri
     * @see PhpBrowser::seeInCurrentUrl()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function seeInCurrentUrl($uri) {
        $this->scenario->assertion('seeInCurrentUrl', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
    }

 
    /**
     * Attaches file from Codeception data directory to upload field.
     *
     * Example:
     *
     * ``` php
     * <?php
     * // file is stored in 'tests/data/tests.xls'
     * $I->attachFile('prices.xls');
     * ?>
     * ```
     *
     * @param $field
     * @param $filename
     * @see PhpBrowser::attachFile()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function attachFile($field, $filename) {
        $this->scenario->action('attachFile', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
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
     * $I->seeCheckboxIsChecked('//form/input[@type=checkbox and @name=agree]');
     *
     * ```
     *
     * @param $checkbox
     * @see PhpBrowser::seeCheckboxIsChecked()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function seeCheckboxIsChecked($checkbox) {
        $this->scenario->assertion('seeCheckboxIsChecked', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
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
     * @param $checkbox
     * @see PhpBrowser::dontSeeCheckboxIsChecked()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function dontSeeCheckboxIsChecked($checkbox) {
        $this->scenario->action('dontSeeCheckboxIsChecked', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
    }

 
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
     * @see PhpBrowser::seeInField()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function seeInField($field, $value) {
        $this->scenario->assertion('seeInField', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
    }

 
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
     * @see PhpBrowser::dontSeeInField()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function dontSeeInField($field, $value) {
        $this->scenario->action('dontSeeInField', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
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
     * @see PhpBrowser::grabTextFrom()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function grabTextFrom($cssOrXPathOrRegex) {
        $this->scenario->action('grabTextFrom', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
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
     * @see PhpBrowser::grabValueFrom()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function grabValueFrom($field) {
        $this->scenario->action('grabValueFrom', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
    }

 
    /**
     *
     * @see PhpBrowser::grabAttribute()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function grabAttribute() {
        $this->scenario->action('grabAttribute', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
    }

 
    /**
     * Enters a directory In local filesystem.
     * Project root directory is used by default
     *
     * @param $path
     * @see Filesystem::amInPath()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function amInPath($path) {
        $this->scenario->condition('amInPath', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
    }

 
    /**
     * Opens a file and stores it's content.
     *
     * Usage:
     *
     * ``` php
     * <?php
     * $I->openFile('composer.json');
     * $I->seeInThisFile('codeception/codeception');
     * ?>
     * ```
     *
     * @param $filename
     * @see Filesystem::openFile()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function openFile($filename) {
        $this->scenario->action('openFile', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
    }

 
    /**
     * Deletes a file
     *
     * ``` php
     * <?php
     * $I->deleteFile('composer.lock');
     * ?>
     * ```
     *
     * @param $filename
     * @see Filesystem::deleteFile()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function deleteFile($filename) {
        $this->scenario->action('deleteFile', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
    }

 
    /**
     * Deletes directory with all subdirectories
     *
     * ``` php
     * <?php
     * $I->deleteDir('vendor');
     * ?>
     * ```
     *
     * @param $dirname
     * @see Filesystem::deleteDir()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function deleteDir($dirname) {
        $this->scenario->action('deleteDir', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
    }

 
    /**
     * Copies directory with all contents
     *
     * ``` php
     * <?php
     * $I->copyDir('vendor','old_vendor');
     * ?>
     * ```
     *
     * @param $src
     * @param $dst
     * @see Filesystem::copyDir()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function copyDir($src, $dst) {
        $this->scenario->action('copyDir', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
    }

 
    /**
     * Checks If opened file has `text` in it.
     *
     * Usage:
     *
     * ``` php
     * <?php
     * $I->openFile('composer.json');
     * $I->seeInThisFile('codeception/codeception');
     * ?>
     * ```
     *
     * @param $text
     * @see Filesystem::seeInThisFile()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function seeInThisFile($text) {
        $this->scenario->assertion('seeInThisFile', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
    }

 
    /**
     * Checks If opened file doesn't contain `text` in it
     *
     * ``` php
     * <?php
     * $I->openFile('composer.json');
     * $I->seeInThisFile('codeception/codeception');
     * ?>
     * ```
     *
     * @param $text
     * @see Filesystem::dontSeeInThisFile()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function dontSeeInThisFile($text) {
        $this->scenario->action('dontSeeInThisFile', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
    }

 
    /**
     * Deletes a file
     * @see Filesystem::deleteThisFile()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function deleteThisFile() {
        $this->scenario->action('deleteThisFile', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
    }

 
    /**
     * Checks if file exists in path.
     * Opens a file when it's exists
     *
     * ``` php
     * <?php
     * $I->seeFileFound('UserModel.php','app/models');
     * ?>
     * ```
     *
     * @param $filename
     * @param string $path
     * @see Filesystem::seeFileFound()
     *
     * ! This method is generated. DO NOT EDIT. !
     * ! Documentation taken from corresponding module !
     */
    public function seeFileFound($filename, $path = null) {
        $this->scenario->assertion('seeFileFound', func_get_args());
        if ($this->scenario->running()) {
            $result = $this->scenario->runStep();
            return new Maybe($result);
        }
        return new Maybe();
    }
}

