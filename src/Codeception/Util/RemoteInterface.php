<?php
namespace Codeception\Util;

interface RemoteInterface
{
    public function _getUrl();

    public function _setCookie($cookie, $value);

    public function _setHeader($header, $value);

    public function _getResponseHeader($header);

    public function _getResponseCode();

    public function _sendRequest($url);
}
