<?php

namespace Codeception\Util\Connector;

use Symfony\Component\BrowserKit\Response;
use Yii;

/**
 *
 *
 *
 *
 */
class Yii1 extends \Symfony\Component\BrowserKit\Client
{

	/**
	 * http://localhost/path/to/your/app/index.php
	 * @var string url of the entry Yii script
	 */
	public $url;

	/**
	 * Current application settings {@see Codeception\Module\Yii1::$appSettings}
	 * @var array
	 */
	public $appSettings;

	/**
	 * Full path to your application
	 * @var string
	 */
	public $appPath;

	/**
	 * Current request headers
	 * @var array
	 */
	private $_headers;

	/**
	 *
	 * @param Symfony\Component\BrowserKit\Request $request
	 * @return \Symfony\Component\BrowserKit\Response
	 */
	public function doRequest($request)
	{
		$this->_headers = array();
		$_COOKIE = $request->getCookies();
		$_SERVER = $request->getServer();
		$_FILES = $request->getFiles();
		$_REQUEST = $request->getParameters();

		if (strtoupper($request->getMethod()) == 'GET')
			$_GET = $request->getParameters();
		else
			$_POST = $request->getParameters();

		$queryString = parse_url($uri,PHP_URL_QUERY);
		parse_str($queryString,$params);

		$uri = str_replace('http://localhost','',$request->getUri());
		$scriptName =  str_replace('http://localhost','',$this->url);

		if (strpos($uri,$scriptName) === false)
			$uri = $scriptName.$queryString;

		foreach($params as $k=>$v)
			$_GET[$k] = $v;

		$_SERVER['REQUEST_METHOD'] = strtoupper($request->getMethod());
		$_SERVER['REQUEST_URI'] = $uri;

		/**
		 * Hack to be sure that CHttpRequest will resolve route correctly
		 */
		$_SERVER['SCRIPT_NAME'] = $scriptName;
		$_SERVER['SCRIPT_FILENAME'] = $this->appPath;

		ob_start();

		Yii::setApplication(null);
		Yii::createApplication($this->appSettings['class'],$this->appSettings['config']);
		Yii::app()->onEndRequest->add(array($this,'setHeaders'));
		Yii::app()->run();

		$content = ob_get_clean();

		$response = new Response($content,200,$this->getHeaders());
		return $response;
	}

	public function setHeaders()
	{
		$this->_headers = Yii::app()->request->getHeaders();
	}

	public function getHeaders()
	{
		return $this->_headers;
	}

}
