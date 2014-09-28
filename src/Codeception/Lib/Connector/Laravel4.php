<?php
namespace Codeception\Lib\Connector;

use Illuminate\Foundation\Testing\Client;

class Laravel4 extends Client
{
    protected function doRequest($request)
    {
        $headers = $request->headers;

        $this->fireBootedCallbacks();
        $response = parent::doRequest($request);

        // saving referer for redirecting back
        if (!$this->getHistory()->isEmpty()) {
            $headers->set('referer', $this->getHistory()->current()->getUri());
        }
        return $response;
    }

     protected function fireBootedCallbacks()
    {
        $bootedCallbacks = new \ReflectionProperty($this->kernel, 'bootedCallbacks');
        $bootedCallbacks->setAccessible(true);
        $callbacks = $bootedCallbacks->getValue($this->kernel);
        foreach ($callbacks as $callback) {
            call_user_func($callback, $this->kernel);
        }

    }

}