<?php


class CodeceptionHttpRequest extends \CHttpRequest
{

	private $_headers = array();

	protected $_cookies;

	public function setHeader($name,$value)
	{
		$this->_headers[$name] = $value;
	}

	public function getHeader($name,$default=null)
	{
		return isset($this->_headers[$name])? $this->_headers[$name] : $default;
	}

	public function getAllHeaders()
	{
		return $this->_headers;
	}

	public function getCookies()
	{
		if($this->_cookies!==null)
			return $this->_cookies;
		else
			return $this->_cookies=new CodeceptionCookieCollection($this);
	}

	public function redirect($url, $terminate = true, $statusCode = 302)
	{
		$this->setHeader('Location', $url);
		if($terminate)
			Yii::app()->end(0,false);
	}

}

class CodeceptionCookieCollection extends CCookieCollection
{

	protected function addCookie($cookie)
	{
		$_COOKIE[$cookie->name] = $cookie->value;
	}

	protected function removeCookie($cookie)
	{
		unset($_COOKIE[$cookie->name]);
	}

}