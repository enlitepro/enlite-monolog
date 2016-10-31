<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonolog\Service;


use Closure;
use Exception;
use Interop\Container\ContainerInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Monolog\Formatter\FormatterInterface;
use RuntimeException;
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
        return $this->createLogger($serviceLocator, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var MonologOptions $options */
        $options = $container->get('EnliteMonologOptions');
        return $this->createLogger($container, $options);
    }

    /**
     * @param ServiceLocatorInterface|ContainerInterface $container
     * @param MonologOptions $options
     * @return Logger
     */
    public function createLogger($container, MonologOptions $options)
    {
        $logger = new Logger($options->getName());

        $handlers = array();
        foreach ($options->getHandlers() as $handler) {
            $handlers[] = $this->createHandler($container, $options, $handler);
        }
        $logger->setHandlers($handlers);

        foreach ($options->getProcessors() as $processor) {
            $logger->pushProcessor($this->createProcessor($container, $processor));
        }

        return $logger;
    }

    /**
     * @param ServiceLocatorInterface|ContainerInterface $container
     * @param MonologOptions $options
     * @param string|array $handler
     * @throws \RuntimeException
     * @return HandlerInterface
     *
     */
    public function createHandler($container, MonologOptions $options, $handler)
    {
        if (is_string($handler) && $container->has($handler)) {
            return $container->get($handler);
        } else {
            if (!isset($handler['name'])) {
                throw new RuntimeException('Cannot create logger handler');
            }

            if (!class_exists($handler['name'])) {
                throw new RuntimeException('Cannot create logger handler (' . $handler['name'] . ')');
            }

            if (isset($handler['args'])) {
                if (!is_array($handler['args'])) {
                    throw new RuntimeException('Arguments of handler(' . $handler['name'] . ') must be array');
                }

                $reflection = new \ReflectionClass($handler['name']);

                if (isset($handler['args']['handler'])) {
                    foreach ($options->getHandlers() as $key => $option) {
                        if ($handler['args']['handler'] == $key) {
                            $handler['args']['handler'] = $this->createHandler($container, $options, $option);
                            break;
                        }
                    }
                }

                $parameters = array();
                $handlerOptions = $handler['args'];

                $requiredArgsCount = $reflection->getConstructor()->getNumberOfRequiredParameters();

                if ($requiredArgsCount > sizeof($handlerOptions)) {
                    throw new RuntimeException(sprintf('Handler(%s) requires at least %d params. Only %d passed.', $handler['name'], $requiredArgsCount, sizeof($handlerOptions)));
                }

                foreach($reflection->getConstructor()->getParameters() as $parameter) {
                    if (!$parameter->isOptional() && !isset($handlerOptions[$parameter->getName()])) {
                        $argumentValue = array_shift($handlerOptions);
                    } elseif (isset($handlerOptions[$parameter->getName()])) {
                        $argumentValue = $handlerOptions[$parameter->getName()];
                        unset($handlerOptions[$parameter->getName()]);
                    } else {
                        $argumentValue = $parameter->getDefaultValue();
                    }
                    $parameters[$parameter->getPosition()] = $argumentValue;
                }
                $instance = $reflection->newInstanceArgs($parameters);
            } else {
	            $class = $handler['name'];

	            $instance = new $class();
            }

	        if (isset($handler['formatter'])) {
		        $formatter = $this->createFormatter($container, $handler['formatter']);
		        $instance->setFormatter($formatter);
	        }

            return $instance;
        }
    }

	/**
	 * @param ServiceLocatorInterface|ContainerInterface $container
	 * @param string|array $formatter
	 * @return FormatterInterface
	 *
	 * @throws RuntimeException
	 */
	public function createFormatter($container, $formatter)
	{
		if (is_string($formatter) && $container->has($formatter)) {
			return $container->get($formatter);
		} else {
			if (!isset($formatter['name'])) {
				throw new RuntimeException('Cannot create logger formatter');
			}

			if (!class_exists($formatter['name'])) {
				throw new RuntimeException('Cannot create logger formatter (' . $formatter['name'] . ')');
			}

			if (isset($formatter['args'])) {
				if (!is_array($formatter['args'])) {
					throw new RuntimeException('Arguments of formatter(' . $formatter['name'] . ') must be array');
				}

				$reflection = new \ReflectionClass($formatter['name']);

				return call_user_func_array(array($reflection, 'newInstance'), $formatter['args']);
			}

			$class = $formatter['name'];

			return new $class();
		}
	}

    /**
     * @param ServiceLocatorInterface|ContainerInterface $container
     * @param $processor
     * @return Closure
     *
     * @throws RuntimeException
     */
    public function createProcessor($container, $processor)
    {
        if ($processor instanceof Closure) {
            return $processor;
        }

        if (is_string($processor)) {
            try {
                $instance = $container->get($processor);
            } catch (Exception $ex) {
                $instance = null;
            }

            if ($instance && is_callable($instance)) {
                return $instance;
            }

            $processor = new $processor();

            if (is_callable($processor)) {
                return $processor;
            }
        }

        throw new RuntimeException('Unknown processor type, must be a Closure or the FQCN of an invokable class');
    }
}
