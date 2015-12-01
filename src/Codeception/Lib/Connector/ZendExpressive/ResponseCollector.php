<?php
namespace Codeception\Lib\Connector\ZendExpressive;

use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\EmitterInterface;

class ResponseCollector implements EmitterInterface
{
    /**
     * @var ResponseInterface
     */
    private $response;

    public function emit(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function getResponse()
    {
        if ($this->response === null) {
            throw new \LogicException('Response wasn\'t emitted yet');
        }
        return $this->response;
    }

    public function clearResponse()
    {
        $this->response = null;
    }
}
