<?php

namespace Codeception\Util\Connector;

use Symfony\Component\BrowserKit\Request;
use Symfony\Component\BrowserKit\Response;

class Kohana extends \Symfony\Component\BrowserKit\Client {

	public function setIndex($index) {
		$this->index = $index;
	}

	public function doRequest($request) {
		
		$_COOKIE = $request->getCookies();
		$_SERVER = $request->getServer();
		$_FILES = $request->getFiles();


		$uri = str_replace('http://localhost', '', $request->getUri());

		if (strtoupper($request->getMethod()) == 'GET') {
			$_GET = $request->getParameters();
		}
		if (strtoupper($request->getMethod()) == 'POST') {
			$_POST = $request->getParameters();
		}

	    $_SERVER['KOHANA_ENV'] = 'testing';
		$_SERVER['REQUEST_METHOD'] = strtoupper($request->getMethod());
		$_SERVER['REQUEST_URI'] = strtoupper($uri);

		$this->_initRequest();
		
		$content = \Request::factory($uri)
					->execute()
					->send_headers()
					->body();
		
		$headers = headers_list();
		$headers['Content-type'] = "text/html; charset=UTF-8";
		// header_remove();
		$response = new Response($content, 200, $headers);
		return $response;
	}

	protected function _initRequest() {
		static $is_first_call;
		if ($is_first_call === Null) {
			$is_first_call = true;
		}
		if ($is_first_call) {
			$is_first_call = false;
			ob_start();
			include $this->index;
			ob_end_clean();
		} 
	}

}
