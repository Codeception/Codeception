<?php
namespace Codeception\Lib\Connector;

class Symfony2 extends \Symfony\Component\HttpKernel\Client
{
    private static $hasPerformedRequest;

    public $persistentServices = [];

    protected function doRequest($request)
    {
        $services = [];
        if (static::$hasPerformedRequest) {
            $services = $this->persistServices();
            $this->kernel->shutdown();
        } else {
            static::$hasPerformedRequest = true;
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
     * @param $services
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
     * @param $services
     * @param $container
     */
    protected function injectPersistedServices($services)
    {
        foreach ($services as $serviceName => $service) {
            $this->kernel->getContainer()->set($serviceName, $service);
        }
    }


}