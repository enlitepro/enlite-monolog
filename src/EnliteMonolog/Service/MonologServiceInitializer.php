<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonolog\Service;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Initializer\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

final class MonologServiceInitializer implements InitializerInterface
{
    /**
     * @param mixed $instance
     */
    public function __invoke(ContainerInterface $container, $instance)
    {
        $this->setMonologService($container, $instance);
    }

    /**
     * @param ServiceLocatorInterface|ContainerInterface $container
     * @param mixed $instance
     */
    private function setMonologService($container, $instance): void
    {
        if ($instance instanceof MonologServiceAwareInterface) {
            $instance->setMonologService($container->get('EnliteMonologService'));
        }
    }
}
