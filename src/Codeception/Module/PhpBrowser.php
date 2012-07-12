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

class PhpBrowser extends \Codeception\Util\Mink implements \Codeception\Util\FrameworkInterface {

    protected $requiredFields = array('url');

    public function _cleanup() {
        $driver = new \Behat\Mink\Driver\GoutteDriver();
        $this->session = new \Behat\Mink\Session($driver);
    }

    public function submitForm($selector, $params) {

        $form = $this->session->getPage()->find('css',$selector);
	    $fields = $this->session->getPage()->findAll('css', $selector.' input');
	    $url = '';
	    foreach ($fields as $field) {
		    $url .= sprintf('%s=%s',$field->getAttribute('name'), $field->getAttribute('value')).'&';
	    }

	    $fields = $this->session->getPage()->findAll('css', $selector.' textarea');
	    foreach ($fields as $field) {
		    $url .= sprintf('%s=%s',$field->getAttribute('name'), $field->getValue()).'&';
	    }

	    $fields = $this->session->getPage()->findAll('css', $selector.' select');
        foreach ($fields as $field) {
   		    $url .= sprintf('%s=%s',$field->getAttribute('name'), $field->getValue()).'&';
   	    }

		$url .= '&'.http_build_query($params);
	    parse_str($url, $params);
        $url = $form->getAttribute('action');
        $method = $form->getAttribute('method');

        $this->call($url, $method, $params);
    }

    public function sendAjaxPostRequest($uri, $params = array()) {
        $this->session->setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        $this->call($uri, 'POST', $params);
        $this->debug($this->session->getPage()->getContent());
    }

    public function sendAjaxGetRequest($uri, $params = array()) {
        $this->session->setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        $query = $params ? '?'. http_build_query($params) : '';
        $this->call($uri.$query, 'GET', $params);
        $this->debug($this->session->getPage()->getContent());
    }


	protected function call($uri, $method = 'get', $params = array())
	{
        if (strpos($uri,'#')) $uri = substr($uri,0,strpos($uri,'#'));
        $browser = $this->session->getDriver()->getClient();

    	$this->debug('Request ('.$method.'): '.$uri.' '. json_encode($params));
		$browser->request($method, $uri, $params);


		$this->debug('Response code: '.$this->session->getStatusCode());
	}

	public function _failed(\Codeception\TestCase $test, $fail) {
		file_put_contents(\Codeception\Configuration::logDir().basename($test->getFileName()).'.page.fail.html', $this->session->getPage()->getContent());
	}


}
