<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonolog\Service;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Interop\Container\Exception\NotFoundException;
use Laminas\ServiceManager\AbstractFactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

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
        return $this->has($serviceLocator, $requestedName);
    }

    /**
     * {@inheritdoc}
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return $this->has($container, $requestedName);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @return bool
     * @throws ContainerException
     * @throws NotFoundException
     */
    private function has($container, $requestedName)
    {
        $config = $this->getConfig($container);
        return isset($config[$requestedName]);
    }

    /**
     * {@inheritdoc}
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return $this->createLogger($serviceLocator, $requestedName);
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return $this->createLogger($container, $requestedName);
    }

    private function createLogger($container, $requestedName)
    {
        $config = $this->getConfig($container);

        $factory = new MonologServiceFactory();
        return $factory->createLogger($container, new MonologOptions($config[$requestedName]));
    }

    /**
     * @param ContainerInterface $container
     * @return array
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function getConfig($container)
    {
        if (null !== $this->config) {
            return $this->config;
        }

        $config = $container->get('config');

        if (isset($config['EnliteMonolog'])) {
            $this->config = $config['EnliteMonolog'];
        } else {
            $this->config = array();
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
