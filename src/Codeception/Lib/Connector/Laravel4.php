<?php
namespace Codeception\Lib\Connector;

use Illuminate\Foundation\Testing\Client;
use Stack\Builder;
use Symfony\Component\HttpKernel\TerminableInterface;

class Laravel4 extends Client
{
    protected function doRequest($request)
    {
        $headers = $request->headers;

        $this->fireBootedCallbacks();

        $response = $this->getStackedKernel()->handle($request);
        if ($this->kernel instanceof TerminableInterface) {
            $this->kernel->terminate($request, $response);
        }

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

    /**
     * use stacked kernel to include middlewares
     *
     * @return \Stack\StackedHttpKernel
     */
    protected function getStackedKernel()
    {
        /** @see \Illuminate\Foundation\Application::getStackedClient */
        $middlewaresProperty = new \ReflectionProperty($this->kernel, 'middlewares');
        $middlewaresProperty->setAccessible(true);

        $middlewares = $middlewaresProperty->getValue($this->kernel);

        $stack = new Builder();
        foreach ($middlewares as $middleware) {
            list($class, $parameters) = array_values($middleware);

            array_unshift($parameters, $class);

            call_user_func_array(array($stack, 'push'), $parameters);
        }

        return $stack->resolve($this->kernel);
    }

}