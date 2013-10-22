<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonolog\Service;


use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MonologServiceAbstractFactory implements AbstractFactoryInterface
{

    /**
     * @var array
     */
    protected $config;

    /**
     * {@inheritdoc}
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $config = $this->getConfig($serviceLocator);
        return isset($config[$requestedName]);
    }

    /**
     * {@inheritdoc}
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $config = $this->getConfig($serviceLocator);

        $factory = new MonologServiceFactory();
        return $factory->createLogger($serviceLocator, new MonologOptions($config[$requestedName]));
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return array
     */
    public function getConfig(ServiceLocatorInterface $serviceLocator)
    {
        if (null !== $this->config) {
            return $this->config;
        }

        $config = $serviceLocator->get('config');

        if (isset($config['EnliteMonolog'])) {
            $this->config = $config['EnliteMonolog'];
        } else {
            $this->config = [];
        }

        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

}