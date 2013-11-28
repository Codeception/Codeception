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
		$_COOKIE = array_merge($_COOKIE,$request->getCookies());
		$_SERVER = array_merge($_SERVER,$request->getServer());
		$_FILES = $request->getFiles();
		$_REQUEST = $request->getParameters();

		if (strtoupper($request->getMethod()) == 'GET')
			$_GET = $request->getParameters();
		else
			$_POST = $request->getParameters();

		// Parse url parts
		$uriPath = trim(parse_url($request->getUri(), PHP_URL_PATH), '/');
		$uriQuery = ltrim(parse_url($request->getUri(), PHP_URL_QUERY), '?');
		$scriptName = trim(parse_url($this->url, PHP_URL_PATH), '/');
		if (!empty($uriQuery)) {

		    $uriPath .= '?' . $uriQuery;

		    parse_str($uriQuery, $params);
		    foreach ($params as $k => $v) {
		        $_GET[$k] = $v;
		    }
		}

		// Add script name to request if none
		if (strpos($uriPath, $scriptName) === false) {
		    $uriPath = "{$scriptName}/{$uriPath}";
		}

		$_SERVER['REQUEST_METHOD'] = strtoupper($request->getMethod());
		$_SERVER['REQUEST_URI'] = $uriPath;

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

        $headers = $this->getHeaders();
        $statusCode = 200;
        foreach ($headers as $header => $val) {
            if ($header == 'Location') {
                $statusCode = 302;
            }
        }

        $response = new Response($content, $statusCode, $this->getHeaders());

		return $response;
	}

	/**
	 * Set current client headers when terminating yii application (onEndRequest)
	 */
	public function setHeaders()
	{
		$this->_headers = Yii::app()->request->getAllHeaders();
	}

	/**
	 * Returns current client headers
	 * @return array headers
	 */
	public function getHeaders()
	{
		return $this->_headers;
	}

}
