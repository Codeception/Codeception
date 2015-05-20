<?php
namespace Codeception\Module;

use Codeception\Module;
use Codeception\Lib\Framework as Framework;

/**
 * Module that interacts with Symfony 1.4 applications.
 *
 * Replaces functional testing framework from symfony. Authorization features uses Doctrine and sfDoctrineGuardPlugin.
 * Uses native symfony connections and test classes. Provides additional informations on every actions.
 *
 * If test fails stores last shown page in 'log' dir.
 *
 * Please note, this module doesn't implement standard frameworks interface.
 *
 * ## Status
 *
 * * Maintainer: **davert**
 * * Stability: **stable**
 * * Contact: codecept@davert.mail.ua
 *
 * ## Configuration
 *
 * * app *required* - application you want to test. In most cases it will be 'frontend'
 *
 * ## Public Properties
 * * browser - current instance of sfBrowser class.
 *
 */

class Symfony1 extends Module
{
    /**
     * @api
     * @var \sfBrowser
     */
    public $browser;

    protected $session_id;

    protected $config = array('app' => 'frontend');

    public function _initialize()
    {
        if (!file_exists('config/ProjectConfiguration.class.php')) throw new \Codeception\Exception\Module('Symfony1', 'config/ProjectConfiguration.class.php not found. This file is required for running symfony1');
        require_once('config/ProjectConfiguration.class.php');
        $conf = \ProjectConfiguration::getApplicationConfiguration($this->config['app'], 'test', true);
        \sfContext::createInstance($conf, 'default');
        \sfContext::switchTo('default');

        // chdir(\sfConfig::get('sf_web_dir'));
        $this->browser = new \sfBrowser();
        $this->browser->get('/');
        \sfForm::disableCSRFProtection();
    }

    public function _cleanup()
    {
        chdir(\sfConfig::get('sf_web_dir'));
        $this->browser->restart();
        $this->session_id = $_SERVER['session_id'];
    }

    public function _before(\Codeception\TestCase $test)
    {
        $this->browser->get('/');
    }

    public function _failed(\Codeception\TestCase $test, $fail)
    {
        $output = \Codeception\Configuration::outputDir() . DIRECTORY_SEPARATOR . basename($test->getFileName()) . '.page.debug.html';
        file_put_contents($output, $this->browser->getResponse()->getContent());
    }

    public function _after(\Codeception\TestCase $test)
    {

        $this->browser->restart();
        $this->browser->resetCurrentException();
        $this->browser->get('/');

    }

    protected function call($uri, $method = 'get', $params = array())
    {
        // multi testguy implementation
        $_SERVER['session_id'] = $this->session_id;

        if (false === ($empty = $this->browser->checkCurrentExceptionIsEmpty())) {
            \PHPUnit_Framework_Assert::fail(sprintf('last request threw an uncaught exception "%s: %s"', get_class($this->browser->getCurrentException()), $this->browser->getCurrentException()->getMessage()));
            return;
        }

        $this->debug('Request (' . $method . '): ' . $uri . ' ' . json_encode($params));

        $this->browser->call($uri, $method, $params);
        $this->debug('Response code: ' . $this->browser->getResponse()->getStatusCode());
        $this->debug('Response message: ' . $this->browser->getResponse()->getStatusText());

        if ($form = $this->getForm()) {
            foreach ($form->getErrorSchema() as $field => $desc) {
                $this->debug("Error in form $field field: '$desc'");
            }
        }

        $this->followRedirect();
    }

    protected function followRedirect()
    {
        if ($this->browser->getResponse()->getStatusCode() == 302) {
            $this->debug("redirected to " . $this->browser->getResponse()->getHttpHeader('Location'));
            $this->call($this->browser->getResponse()->getHttpHeader('Location'));
        }
    }

    /**
     * Opens the page.
     *
     * @param $page
     */
    public function amOnPage($page)
    {
        $this->browser->get($page);
        $this->debug('Response code: ' . $this->browser->getResponse()->getStatusCode());
        $this->followRedirect();
    }

    /**
     * Click on link or button and move to next page.
     * Either link text, css selector, or xpath can be passed
     *
     * @param $link
     */
    public function click($link)
    {
        $this->browser->click($link);
        $this->debug('moved to page ' . $this->browser->fixUri($this->browser->getRequest()->getUri()));
        $this->followRedirect();
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
    public function dontSee($text, $selector = null)
    {
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
    public function see($text, $selector = null)
    {
        $res = $this->proceedSee($text, $selector);
        $this->assert($res);
    }

    protected function proceedSee($text, $selector = null)
    {
        $response = $this->browser->getResponse()->getContent();

        if ($selector) {
            $nodes = $this->browser->getResponseDomCssSelector()->matchAll($selector);
            $values = '';
            foreach ($nodes as $node) {
                $values .= '<!-- Merged Output -->'.trim($node->nodeValue);
            }
            $response = Framework::formatResponse($response);

            return array('pageContains', $text, $values, "'$selector' on $response");
        }

        $response = Framework::formatResponse($response);

        return array('pageContains', $text, strip_tags($this->browser->getResponse()->getContent()), "on $response.");
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
        $nodes = $this->browser->getResponseDomCssSelector()->matchAll('a')->getNodes();
        foreach ($nodes as $node) {
            if (0 === strrpos($node->getAttribute('href'), $url)) {
                return \PHPUnit_Framework_Assert::assertContains($text, $node->nodeValue, "with url '$url'");
            }
        }
        \PHPUnit_Framework_Assert::fail($text, "with url '$url'");
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
        $nodes = $this->browser->getResponseDomCssSelector()->matchAll('a')->getNodes();
        foreach ($nodes as $node) {
            if (0 === strrpos($node->getAttribute('href'), $url) && ($node->nodeValue == trim($text))) {
                return \PHPUnit_Framework_Assert::assertFalse((0 === strrpos($node->getAttribute('href'), $url) && ($node->nodeValue == trim($text))), "link with url '$url'");
            }
        }
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
    public function seeCheckboxIsChecked($selector)
    {
        $res = $this->proceedCheckboxIsChecked($selector);
        $this->assert($res);
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
    public function dontSeeCheckboxIsChecked($selector)
    {
        $res = $this->proceedCheckboxIsChecked($selector);
        $this->assertNot($res);
    }

    protected function proceedCheckboxIsChecked($selector)
    {
        $node = $this->browser->getResponseDomCssSelector()->matchSingle($selector)->getNode();
        if (!$node) {
            $this->fail("there is no element $selector on page");
        }
        if ($node->getAttribute('type') != 'checkbox') {
            $this->fail("there is no checkbox $selector on page");
        }
        return array('equals', $node->getAttribute('checked'), 'checked', "that checkbox $selector is checked");
    }

    /**
     * Checks if there were at least one email sent through Symfony test mailer.
     */
    public function seeEmailReceived()
    {
        $this->debug('Emails sent: ' . $this->browser->getContext()->getMailer()->getLogger()->countMessages());
        \PHPUnit_Framework_Assert::assertGreaterThan(0, $this->browser->getContext()->getMailer()->getLogger()->countMessages(), "");
    }


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
     */
    public function submitForm($selector, $params)
    {

        $form = $this->browser->getResponseDomCssSelector()->matchSingle($selector)->getNode();
        if (!$form) \PHPUnit_Framework_Assert::fail("Form by selector '$selector' not found");
        $fields = $this->browser->getResponseDomCssSelector()->matchAll($selector . ' input')->getNodes();
        $url = '';
        foreach ($fields as $field) {
            if ($field->getAttribute('type') == 'checkbox') continue;
            if ($field->getAttribute('type') == 'radio') continue;
            $url .= sprintf('%s=%s', $field->getAttribute('name'), $field->getAttribute('value')) . '&';
        }

        $fields = $this->browser->getResponseDomCssSelector()->matchAll($selector . ' textarea')->getNodes();
        foreach ($fields as $field) {
            $url .= sprintf('%s=%s', $field->getAttribute('name'), $field->nodeValue) . '&';
        }

        $fields = $this->browser->getResponseDomCssSelector()->matchAll($selector . ' select')->getNodes();
        foreach ($fields as $field) {
            foreach ($field->childNodes as $option) {
                if ($option->getAttribute('selected') == 'selected') {
                    $url .= sprintf('%s=%s', $field->getAttribute('name'), $option->getAttribute('value')) . '&';
                    break;
                }
            }
        }

        $url .= '&' . http_build_query($params);
        parse_str($url, $params);
        $url = $form->getAttribute('action');
        $method = $form->getAttribute('method');


        $this->call($url, $method, $params);
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
     */
    public function sendAjaxPostRequest($uri, $params)
    {
        $this->browser->setHttpHeader('X-Requested-With', 'XMLHttpRequest');
        $this->call($uri, 'post', $params);
        $this->debug($this->browser->getResponse()->getContent());
    }


    /**
     * If your page triggers an ajax request, you can perform it manually.
     * This action sends a GET ajax request with specified params.
     *
     * See ->sendAjaxPostRequest for examples.
     *
     * @param $uri
     * @param $params
     */
    public function sendAjaxGetRequest($uri, $params)
    {
        $this->browser->setHttpHeader('X-Requested-With', 'XMLHttpRequest');
        $this->call($uri, 'get', $params);
        $this->debug($this->browser->getResponse()->getContent());
    }

    /**
     * Emulates click on form's submit button.
     * You don't need that action if you fill form by ->submitForm action.
     *
     * @param $selector
     */

    public function clickSubmitButton($selector)
    {
        $this->browser->click($selector);
    }

    /**
     * Performs validation of Symfony1 form.
     * Matches the first sfForm instance from controller and returns isValid() value.
     */
    public function seeFormIsValid()
    {
        $form = $this->getForm();
        if (!$form) \PHPUnit_Framework_Assert::fail('as any symfony forms at all');
        \PHPUnit_Framework_Assert::isFalse($form->hasErrors(), ', seems like there were errors');
    }

    /**
     * Performs validation of Symfony1 form.
     * Matches the first sfForm instance from controller and returns getErrorSchema() values.
     * Shows all errors in debug.
     */
    public function seeErrorsInForm()
    {
        $form = $this->getForm();
        if (!$form) \PHPUnit_Framework_Assert::fail('as any symfony forms at all');
        \PHPUnit_Framework_Assert::isTrue($form->hasErrors(), ', seems like there were no validation errors');
        foreach ($form->getErrorSchema() as $field => $desc) {
            $this->debug("Error in $field field: '$desc'");
        }
    }

    /**
     * Checks for invalid value in Symfony1 form.
     * Matches the first sfForm instance from controller and returns getErrorSchema() values.
     * Specify field which should contain error message.
     *
     * @param $field
     */
    public function seeErrorInField($field)
    {
        $form = $this->getForm();
        $keys = array();
        foreach ($form->getErrorSchema() as $field => $desc) {
            $keys[] = $field;
        }
        $this->assertContains($field, $keys);
    }

    protected function getForm()
    {
        $action = $this->browser->getContext()->getActionStack()->getLastEntry()->getActionInstance();

        foreach ($action->getVarHolder()->getAll() as $name => $value)
        {
            if ($value instanceof \sfForm && $value->isBound()) {
                $form = $value;
                break;
            }
        }
        if (!isset($form)) return null;
        return $form;
    }

    /**
     * Sign's user in with sfGuardAuth.
     * Uses standard path: /sfGuardAuth/signin for authorization.
     * Provide username and password.
     *
     * @param $username
     * @param $password
     */
    public function signIn($username, $password)
    {
        $this->browser->post('/sfGuardAuth/signin', array('signin' => array('username' => $username, 'password' => $password)));
        $this->debug('session: ' . json_encode($this->browser->getUser()->getAttributeHolder()->getAll()));
        $this->debug('user: ' . json_encode($this->browser->getUser()->getAttributeHolder()->getAll('sfGuardSecurityUser')));
        $this->debug('credentials: ' . json_encode($this->browser->getUser()->getCredentials()));
        $this->browser->get('/');
        $this->followRedirect();
    }

    /**
     * Sign out is performing by triggering '/logout' url.
     *
     */

    public function signOut()
    {
        $this->browser->get('/logout');
        $this->followRedirect();
    }

    /**
     * Log in as sfDoctrineGuardUser.
     * Only name of user should be provided.
     * Fetches user by it's username from sfGuardUser table.
     *
     * @param $name
     * @throws \Exception
     */
    public function amLoggedAs($name)
    {
        if (!class_exists('Doctrine')) throw new \Exception('Doctrine is not installed. Consider using \'signIn\' action instead');
        $user = \Doctrine::getTable('sfGuardUser')->findOneBy('username', $name);
        if (!$user) throw new \Exception("User with name $name was not found in database");
        $user->clearRelated();
        $browser = $this->browser;
        $this->browser->getContext()->getStorage()->initialize($browser->getContext()->getStorage()->getOptions());
        $browser->getUser()->signIn($user);
        $this->debug('session: ' . json_encode($this->browser->getUser()->getAttributeHolder()->getAll()));
        $this->debug('user: ' . json_encode($this->browser->getUser()->getAttributeHolder()->getAll('sfGuardSecurityUser')));
        $this->debug('credentials: ' . json_encode($this->browser->getUser()->getCredentials()));
        $this->browser->getUser()->shutdown();
        $this->browser->getContext()->getStorage()->shutdown();

        $browser->get('/');
        $this->followRedirect();
    }

}
