<?php

namespace EnliteMonologTest\Service;

use EnliteMonolog\Service\MonologServiceAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;

class TraitMock5
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
}
