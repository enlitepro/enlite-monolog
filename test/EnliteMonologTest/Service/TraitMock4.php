<?php

namespace EnliteMonologTest\Service;

use EnliteMonolog\Service\MonologServiceAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;

class TraitMock4
{
    use MonologServiceAwareTrait;

    /** @var ServiceLocatorInterface */
    private $serviceLocator;

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }
}
