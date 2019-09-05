<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonolog\Service;

use Interop\Container\ContainerInterface;
use Monolog\Logger;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

final class MonologServiceAbstractFactory implements AbstractFactoryInterface
{
    /**
     * @var array
     */
    protected $config;


    /**
     * {@inheritdoc}
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return $this->has($container, $requestedName);
    }

    /**
     * @param ServiceLocatorInterface|ContainerInterface $container
     * @param $requestedName
     * @return bool
     */
    private function has($container, $requestedName)
    {
        $config = $this->getConfig($container);
        return isset($config[$requestedName]);
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return $this->createLogger($container, $requestedName);
    }

    private function createLogger($container, $requestedName): Logger
    {
        $config = $this->getConfig($container);

        $factory = new MonologServiceFactory();
        return $factory->createLogger($container, new MonologOptions($config[$requestedName]));
    }

    /**
     * @param ServiceLocatorInterface|ContainerInterface $container
     */
    public function getConfig($container): array
    {
        if (null !== $this->config) {
            return $this->config;
        }

        $config = $container->get('config');

        $this->config = $config['EnliteMonolog'] ?? [];

        return $this->config;
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }
}
