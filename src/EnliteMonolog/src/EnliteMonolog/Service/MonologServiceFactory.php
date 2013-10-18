<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonolog\Service;


use Monolog\Logger;
use Zend\Code\Reflection\ClassReflection;
use Zend\Log\LoggerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MonologServiceFactory implements FactoryInterface
{

    /**
     * {@inheritdoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var MonologOptions $options */
        $options = $serviceLocator->get('EnliteMonologOptions');
        $logger = new Logger($options->getName());

        foreach ($options->getHandlers() as $handler) {
            $logger->pushHandler($this->createHandler($serviceLocator, $handler));
        }

        return $logger;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param string|array $handler
     * @return LoggerInterface
     * @throws \RuntimeException
     */
    public function createHandler(ServiceLocatorInterface $serviceLocator, $handler)
    {
        if (is_string($handler) && $serviceLocator->has($handler)) {
            return $serviceLocator->get($handler);
        } else {
            if (!isset($handler['name'])) {
                throw new \RuntimeException('Cannot create logger handler');
            }

            if (!class_exists($handler['name'])) {
                throw new \RuntimeException('Cannot create logger handler (' . $handler['name'] . ')');
            }

            if (isset($handler['args'])) {
                if (!is_array($handler['args'])) {
                    throw new \RuntimeException('Arguments of handler(' . $handler['name'] . ') must be array');
                }

                $reflection = new ClassReflection($handler['name']);
                return call_user_func_array(array($reflection, 'newInstance'), $handler['args']);
            }

            $class = $handler['name'];

            return new $class();
        }
    }
}