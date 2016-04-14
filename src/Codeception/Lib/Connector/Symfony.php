<?php
namespace Codeception\Lib\Connector;

class Symfony extends \Symfony\Component\HttpKernel\Client
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
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container = null;

    /**
     * @var array
     */
    public $persistentServices = [];

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
        $this->rebootable = (boolean)$rebootable;
        $this->persistentServices = $services;
        $this->rebootKernel();
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    protected function doRequest($request)
    {
        if ($this->rebootable) {
            if ($this->hasPerformedRequest) {
                $this->rebootKernel();
            } else {
                $this->hasPerformedRequest = true;
            }
        }
        return parent::doRequest($request);
    }

    /**
     * Reboot kernel
     *
     * Services from the list of persistent services
     * are updated from service container before kernel shutdown
     * and injected into newly initialized container after kernel boot.
     */
    public function rebootKernel()
    {
        if ($this->container) {
            foreach ($this->persistentServices as $serviceName => $service) {
                if ($this->container->has($serviceName)) {
                    $this->persistentServices[$serviceName] = $this->container->get($serviceName);
                }
            }
        }

        $this->kernel->shutdown();
        $this->kernel->boot();
        $this->container = $this->kernel->getContainer();

        if ($this->container->has('profiler')) {
            $this->container->get('profiler')->enable();
        }

        foreach ($this->persistentServices as $serviceName => $service) {
            $this->container->set($serviceName, $service);
        }
    }
}
