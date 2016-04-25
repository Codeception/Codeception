<?php

namespace Codeception\Lib\Connector\ZF2;

use \Zend\ServiceManager\ServiceLocatorInterface;
use \Zend\ServiceManager\ServiceManager;

class DoctrineServiceManager extends ServiceManager implements ServiceLocatorInterface
{
    
    private $serviceManager;

    public function __construct(ServiceLocatorInterface $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    public function get($name)
    {
        return $this->serviceManager->get($name);
    }

    public function has($name)
    {
        if (preg_match('/doctrine/i', $name)) {
            return $this->serviceManager->has($name);
        }
        return false;
    }
}
