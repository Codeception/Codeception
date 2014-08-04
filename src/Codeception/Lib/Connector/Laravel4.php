<?php
namespace Codeception\Lib\Connector;

use Illuminate\Foundation\Testing\Client;

class Laravel4 extends Client
{

    protected function doRequest($request)
    {
        $this->rebootKernel();
        $this->kernel->setRequestForConsoleEnvironment();
        
        $headers = $request->headers;

        $response = parent::doRequest($request);

        // saving referer for redirecting back
        if (!$this->getHistory()->isEmpty()) {
            $headers->set('referer', $this->getHistory()->current()->getUri());
        }
        return $response;
    }

     protected function rebootKernel()
    {
        $booted = new \ReflectionProperty($this->kernel, 'booted');
        $booted->setAccessible(true);
        $booted->setValue($this->kernel, false);
        $this->kernel->boot();
    }
}