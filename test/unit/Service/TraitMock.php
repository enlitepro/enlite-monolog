<?php

namespace EnliteMonologTest\Service;

use EnliteMonolog\Service\MonologServiceAwareTrait;
use Laminas\ServiceManager\ServiceLocatorAwareInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class TraitMock implements ServiceLocatorAwareInterface
{
    use MonologServiceAwareTrait;

    /** @var ServiceLocatorInterface */
    private $services;

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->services = $serviceLocator;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->services;
    }
}
