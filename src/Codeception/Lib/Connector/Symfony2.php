<?php
namespace Codeception\Lib\Connector;

class Symfony2 extends \Symfony\Component\HttpKernel\Client
{
    /**
     * @var boolean
     */
    private $rebootable = true;

    /**
     * @var boolean
     */
    private $hasPerformedRequest = false;

    /**
     * @var array
     */
    public $persistentServices = [];

    /**
     *  Get container.
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer()
    {
        return $this->kernel->getContainer();
    }

    /**
     * Constructor.
     *
     * @param \Symfony\Component\HttpKernel\Kernel  $kernel     A booted HttpKernel instance
     * @param array                                 $services   An injected services
     * @param boolean                               $rebootable
     */
    public function __construct(\Symfony\Component\HttpKernel\Kernel $kernel, array $services = [], $rebootable = true)
    {
        parent::__construct($kernel);
        $this->followRedirects(true);
        $this->persistentServices = $services;
        $this->rebootable = (boolean) $rebootable;
        $this->injectPersistentServices();
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    protected function doRequest($request)
    {
        if ($this->hasPerformedRequest && $this->rebootable) {
            $this->retrievePersistentServices();
            $this->kernel->shutdown();
            $this->kernel->boot();
            $this->injectPersistentServices();
        } else {
            $this->hasPerformedRequest = true;
        }
        return parent::doRequest($request);
    }

    /**
     *  Retrieve and update persistent services.
     */
    protected function retrievePersistentServices()
    {
        $container = $this->kernel->getContainer();
        foreach ($this->persistentServices as $serviceName => $service) {
            if ($container->has($serviceName)) {
                $this->persistentServices[$serviceName] = $container->get($serviceName);
            }
        }
    }

    /**
     *  Enable the profiler and inject persistent services.
     */
    protected function injectPersistentServices()
    {
        $container = $this->kernel->getContainer();
        if ($container->has('profiler')) {
            $container->get('profiler')->enable();
        }
        foreach ($this->persistentServices as $serviceName => $service) {
            $container->set($serviceName, $service);
        }
    }
}
