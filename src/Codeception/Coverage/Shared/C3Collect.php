<?php
namespace Codeception\Coverage\Shared;
use Codeception\Exception\RemoteException;
use Codeception\Util\WebInterface;

// headers
const COVERAGE_HEADER = 'X-Codeception-CodeCoverage';
const COVERAGE_HEADER_ERROR = 'X-Codeception-CodeCoverage-Error';
const COVERAGE_HEADER_CONFIG = 'X-Codeception-CodeCoverage-Config';
const COVERAGE_HEADER_SUITE = 'X-Codeception-CodeCoverage-Suite';

// cookie names
const COVERAGE_COOKIE = 'CODECEPTION_CODECOVERAGE';
const COVERAGE_COOKIE_ERROR = 'CODECEPTION_CODECOVERAGE_ERROR';

trait C3Collect
{

    /**
     * @var WebInterface
     */
    protected $module;
    protected $http = array('method' => "GET", 'header' => '');

    protected function c3Request($action)
    {
        $this->addHeader('X-Codeception-CodeCoverage', 'remote-access');
        $context = stream_context_create(array('http' => $this->http));
        $contents = file_get_contents($this->module->_getUrl() . '/c3/report/' . $action, false, $context);
        if ($contents === false) {
            $this->getRemoteError($http_response_header);
        }
        return $contents;
    }

    protected function setRequestHeaders($params)
    {
        if (!method_exists($this->module, 'setHeader')) {
            return;
        }
        $this->module->setHeader(COVERAGE_HEADER, $params['test_name']);
        $this->module->setHeader(COVERAGE_HEADER_SUITE, $params['suite_name']);
        if ($params['remote_config']) {
            $this->module->setHeader(COVERAGE_HEADER_CONFIG, $params['remote_config']);
        }
    }

    protected function setRequestCookies($params)
    {
        $cookie = [
            'CodeCoverage'        => $params['test_name'],
            'CodeCoverage_Suite'  => $params['suite_name'],
            'CodeCoverage_Config' => $params['remote_config']
        ];
        $this->module->setCookie(COVERAGE_COOKIE, json_encode($cookie));
    }

    protected function resetCookies()
    {
        if ($error = $this->module->grabCookie(COVERAGE_COOKIE_ERROR)) {
            throw new RemoteException($error);
        }
        $this->module->resetCookie(COVERAGE_COOKIE_ERROR);
        $this->module->resetCookie(COVERAGE_COOKIE);
    }

    protected function getRemoteError($headers)
    {
        foreach ($headers as $header) {
            if (strpos($header, 'X-Codeception-CodeCoverage-Error') === 0) {
                throw new RemoteException($header);
            }
        }
    }

    protected function addRequestParam($param, $value)
    {

    }

    protected function addHeader($header, $value)
    {
        $this->http['header'] .= "$header: $value\r\n";
    }


} 