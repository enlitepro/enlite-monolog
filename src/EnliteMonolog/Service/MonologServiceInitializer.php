<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonolog\Service;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Interop\Container\Exception\NotFoundException;
use Laminas\ServiceManager\Initializer\InitializerInterface;

class MonologServiceInitializer implements InitializerInterface
{
    /**
     * @param ContainerInterface $container
     * @param $instance
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function __invoke(ContainerInterface $container, $instance): void
    {
        if ($instance instanceof MonologServiceAwareInterface) {
            $instance->setMonologService($container->get('EnliteMonologService'));
        }
    }
}
