<?php
namespace Codeception\Coverage;
use Codeception\Exception\RemoteException;

// headers
const COVERAGE_HEADER = 'X-Codeception-CodeCoverage';
const COVERAGE_HEADER_ERROR = 'X-Codeception-CodeCoverage-Error';
const COVERAGE_HEADER_CONFIG = 'X-Codeception-CodeCoverage-Config';
const COVERAGE_HEADER_SUITE = 'X-Codeception-CodeCoverage-Suite';

// cookie names
const COVERAGE_COOKIE = 'CODECEPTION_CODECOVERAGE';
const COVERAGE_COOKIE_ERROR = 'CODECEPTION_CODECOVERAGE_ERROR';

trait C3Connector
{

    protected $http = array('method' => "GET", 'header' => '');

    protected function c3Request($url, $action)
    {
        $this->addHeader('X-Codeception-CodeCoverage', 'remote-access');
        $context = stream_context_create(array('http' => $this->http));
        $contents = file_get_contents($url . '/c3/report/' . $action, false, $context);
        if ($contents === false) {
            $this->getRemoteError($http_response_header);
        }
        return $contents;
    }

    protected function getRemoteError($headers)
    {
        foreach ($headers as $header) {
            if (strpos($header, 'X-Codeception-CodeCoverage-Error') === 0) {
                throw new RemoteException($header);
            }
        }
    }

    protected function addHeader($header, $value)
    {
        $this->http['header'] .= "$header: $value\r\n";
    }


} 