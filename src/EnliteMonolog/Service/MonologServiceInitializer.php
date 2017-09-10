<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonolog\Service;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MonologServiceInitializer implements InitializerInterface
{

    /**
     * Initialize
     *
     * @param $instance
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        $this->setMonologService($serviceLocator, $instance);

        return null;
    }

    /**
     * @param ContainerInterface $container
     * @param $instance
     */
    public function __invoke(ContainerInterface $container, $instance)
    {
        $this->setMonologService($container, $instance);
    }

    /**
     * @param ServiceLocatorInterface|ContainerInterface $container
     * @param $instance
     */
    private function setMonologService($container, $instance)
    {
        if ($instance instanceof MonologServiceAwareInterface) {
            $instance->setMonologService($container->get('EnliteMonologService'));
        }
    }
}
