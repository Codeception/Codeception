<?php

namespace Codeception\Module;

/**
 * Uses Mink (http://mink.behat.org) with Goutte Driver to interact with your application.
 * Contains all Mink actions and additional ones, listed below.
 *
 * Use to perform web acceptance tests with non-javascript browser.
 *
 * If test fails stores last shown page in 'output' dir.
 *
 * ## Configuration
 *
 * * url *required* - start url of your app
 *
 * ## Public Properties
 *
 * * session - contains Mink Session
 *
 */

class PhpBrowser extends \Codeception\Util\Mink {

    protected $requiredFields = array('url');

    public function _cleanup() {
        $zendOptions = array('httpversion' => '1.1', 'useragent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0a2) Gecko/20110613 Firefox/6.0a2');
        if (isset($this->config['zend'])) array_merge($this->config['zend'], $zendOptions);
        $mink = new \Behat\Mink\Mink(array('primary' => new \Behat\Mink\Session(new \Behat\Mink\Driver\GoutteDriver(new \Goutte\Client($zendOptions)))));
        $this->session = $mink->getSession('primary');
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
    public function submitForm($selector, $params) {

        $form = $this->session->getPage()->find('css',$selector);
	    $fields = $this->session->getPage()->findAll('css', $selector.' input');
	    $url = '';
	    foreach ($fields as $field) {
		    if ($field->getAttribute('type') == 'checkbox') continue;
		    if ($field->getAttribute('type') == 'radio') continue;
		    $url .= sprintf('%s=%s',$field->getAttribute('name'), $field->getAttribute('value')).'&';
	    }

	    $fields = $this->session->getPage()->findAll('css', $selector.' textarea');
	    foreach ($fields as $field) {
		    $url .= sprintf('%s=%s',$field->getAttribute('name'), $field->getText()).'&';
	    }

	    $fields = $this->session->getPage()->findAll('css', $selector.' select');
	    foreach ($fields as $field) {
            foreach ($field->childNodes as $option) {
                if ($option->getAttribute('selected') == 'selected')
                    $url .= sprintf('%s=%s',$field->getAttribute('name'), $option->getValue()).'&';

            }
	    }

		$url .= '&'.http_build_query($params);
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
    public function sendAjaxPostRequest($uri, $params = array()) {
        $this->session->setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        $this->call($uri, 'post', $params);
        $this->debug($this->session->getPage()->getContent());
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
    public function sendAjaxGetRequest($uri, $params = array()) {
        $this->session->setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        $this->call($uri, 'get', $params);
        $this->debug($this->session->getPage()->getContent());
    }


	protected function call($uri, $method = 'get', $params = array())
	{
        $browser = $this->session->getDriver()->getClient();
//        $uri = $browser->getAbsoluteUri($uri);

    	$this->debug('Request ('.$method.'): '.$uri.' '. json_encode($params));
		$browser->request($method, $uri, $params);

		$this->debug('Response code: '.$this->session->getStatusCode());
	}

	public function _failed(\Codeception\TestCase $test, $fail) {
		file_put_contents(\Codeception\Configuration::dataDir().$test->getFileName().'.page.debug.html', $this->session->getPage()->getContent());
	}
    
}
