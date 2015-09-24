<?php
namespace Codeception\Lib\Connector;

class Symfony2 extends \Symfony\Component\HttpKernel\Client
{
    
    /**
     * @var boolean
     */
    private static $hasPerformedRequest;

    /**
     * @var array
     */
    public $persistentServices = [];

    /**
     * @param Request $request
     */
    protected function doRequest($request)
    {
        $services = [];
        if (self::$hasPerformedRequest) {
            $services = $this->persistServices();
            $this->kernel = clone $this->kernel;
        } else {
            self::$hasPerformedRequest = true;
        }
        $this->kernel->boot();

        $container = $this->kernel->getContainer();
        if ($this->kernel->getContainer()->has('profiler')) {
            $container->get('profiler')->enable();
        }

        $this->injectPersistedServices($services);

        return parent::doRequest($request);
    }

    /**
     * @return array
     */
    protected function persistServices()
    {
        $services = [];
        foreach ($this->persistentServices as $serviceName) {
            if (!$this->kernel->getContainer()->has($serviceName)) {
                continue;
            }
            $services[$serviceName] = $this->kernel->getContainer()->get($serviceName);
        }
        return $services;
    }

    /**
     * @param array $services
     */
    protected function injectPersistedServices($services)
    {
        foreach ($services as $serviceName => $service) {
            $this->kernel->getContainer()->set($serviceName, $service);
        }
    }
}
