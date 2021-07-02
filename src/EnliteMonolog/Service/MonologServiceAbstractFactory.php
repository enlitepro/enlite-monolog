<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonolog\Service;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\NotFoundException;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;

class MonologServiceAbstractFactory implements AbstractFactoryInterface
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

    private function createLogger($container, $requestedName)
    {
        $config = $this->getConfig($container);

        $factory = new MonologServiceFactory();
        return $factory->createLogger($container, new MonologOptions($config[$requestedName]));
    }

    /**
     * @throws NotFoundException
     */
    public function getConfig(ContainerInterface $container): array
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
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }
}
