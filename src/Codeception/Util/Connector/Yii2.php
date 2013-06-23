<?php

namespace Codeception\Util\Connector;

use Yii;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Response;
use yii\web\Response as YiiResponse;

class Yii2 extends Client
{
	/**
	 * http://localhost/path/to/your/app/index.php
	 * @var string url of the entry Yii script
	 */
	public $url;
	public $entryScript;
	/**
	 * @var array
	 */
	public $headers;
	public $statusCode;

	/**
	 *
	 * @param \Symfony\Component\BrowserKit\Request $request
	 * @return \Symfony\Component\BrowserKit\Response
	 */
	public function doRequest($request)
	{
		$_COOKIE = $request->getCookies();
		$_SERVER = $request->getServer();
		$_FILES = $request->getFiles();
		$_REQUEST = $request->getParameters();

		if (strtoupper($request->getMethod()) == 'GET') {
			$_GET = $request->getParameters();
		} else {
			$_POST = $request->getParameters();
		}

		$uri = str_replace('http://localhost', '', $request->getUri());
		$scriptName = str_replace('http://localhost', '', $this->url);

		$queryString = parse_url($uri, PHP_URL_QUERY);
		parse_str($queryString, $params);

		if (strpos($uri, $scriptName) === false) {
			$uri = $scriptName . $queryString;
		}

		foreach ($params as $k => $v) {
			$_GET[$k] = $v;
		}

		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$_SERVER['REQUEST_METHOD'] = strtoupper($request->getMethod());
		$_SERVER['REQUEST_URI'] = $uri;
		$_SERVER['SCRIPT_NAME'] = $scriptName;
		$_SERVER['SCRIPT_FILENAME'] = $this->entryScript;
		$_SERVER['PHP_SELF'] = $this->entryScript;

		$config = require($this->entryScript);
		/** @var \yii\web\Application $app */
		$app = Yii::createObject($config);
		$client = $this;
		$app->getResponse()->on(YiiResponse::EVENT_AFTER_PREPARE, function ($event) use ($client) {
			/** @var \yii\web\Response $sender */
			$sender = $event->sender;
			$this->headers = $sender->getHeaders()->toArray();
			$sender->getHeaders()->removeAll();
			$this->statusCode = $sender->getStatusCode();
			$_POST = array();
			$sender->setStatusCode(null);
		});

		$this->headers = array();
		$this->statusCode = null;

		ob_start();
		$app->handleRequest($app->getRequest())->send();
		$content = ob_get_clean();

		return new Response($content, $this->statusCode === null ? 200 : $this->statusCode, $this->headers);
	}
}
