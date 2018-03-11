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
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \RuntimeException
     * @throws \Interop\Container\Exception\NotFoundException
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var MonologOptions $options */
        $options = $serviceLocator->get('EnliteMonologOptions');
        return $this->createLogger($serviceLocator, $options);
    }

    /**
     * {@inheritdoc}
     * @throws \Interop\Container\Exception\NotFoundException
     * @throws \RuntimeException
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
     * @throws \Interop\Container\Exception\NotFoundException
     * @throws \RuntimeException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function createLogger($container, MonologOptions $options)
    {
        $logger = new Logger($options->getName());

        $handlers = array_reverse($options->getHandlers());
        foreach ($handlers as $handler) {
            $logger->pushHandler($this->createHandler($container, $options, $handler));
        }

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
     * @throws \Interop\Container\Exception\NotFoundException
     * @throws \Interop\Container\Exception\ContainerException
     *
     */
    public function createHandler($container, MonologOptions $options, $handler)
    {
        if (is_string($handler) && $container->has($handler)) {
            return $container->get($handler);
        }


        if (!isset($handler['name'])) {
            throw new RuntimeException('Cannot create logger handler');
        }

        $handlerClassName = $handler['name'];

        if (!class_exists($handlerClassName)) {
            throw new RuntimeException('Cannot create logger handler (' . $handlerClassName . ')');
        }

        $arguments = array_key_exists('args', $handler) ? $handler['args'] : array();

        if (!is_array($arguments)) {
            throw new RuntimeException('Arguments of handler(' . $handlerClassName . ') must be array');
        }

        if (isset($arguments['handler'])) {
            foreach ($options->getHandlers() as $key => $option) {
                if ($arguments['handler'] == $key) {
                    $arguments['handler'] = $this->createHandler($container, $options, $option);
                    break;
                }
            }
        }

        try {
            /** @var HandlerInterface $instance */
            $instance = $this->createInstanceFromArguments($handlerClassName, $arguments);
        } catch (\InvalidArgumentException $exception) {
            throw new RuntimeException(sprintf(
                'Handler(%s) has an invalid argument configuration',
                $handlerClassName
            ), 0, $exception);
        }

        if (isset($handler['formatter'])) {
            $formatter = $this->createFormatter($container, $handler['formatter']);
            $instance->setFormatter($formatter);
        }

        return $instance;
    }

    /**
     * @param ServiceLocatorInterface|ContainerInterface $container
     * @param string|array $formatter
     * @return FormatterInterface
     * @throws \Interop\Container\Exception\NotFoundException
     * @throws \Interop\Container\Exception\ContainerException
     * @throws RuntimeException
     */
    public function createFormatter($container, $formatter)
    {
        if (is_string($formatter) && $container->has($formatter)) {
            return $container->get($formatter);
        }

        if (!isset($formatter['name'])) {
            throw new RuntimeException('Cannot create logger formatter');
        }

        $formatterClassName = $formatter['name'];

        if (!class_exists($formatterClassName)) {
            throw new RuntimeException('Cannot create logger formatter (' . $formatterClassName . ')');
        }

        $arguments = array_key_exists('args', $formatter) ? $formatter['args'] : array();

        if (!is_array($arguments)) {
            throw new RuntimeException('Arguments of formatter(' . $formatterClassName . ') must be array');
        }

        try {
            /** @var FormatterInterface $instance */
            $instance = $this->createInstanceFromArguments($formatterClassName, $arguments);
        } catch (\InvalidArgumentException $exception) {
            throw new RuntimeException(sprintf(
                'Formatter(%s) has an invalid argument configuration',
                $formatterClassName
            ), 0, $exception);
        }

        return $instance;
    }

    /**
     * @param ServiceLocatorInterface|ContainerInterface $container
     * @param $processor
     * @return Closure
     * @throws \Interop\Container\Exception\NotFoundException
     * @throws \Interop\Container\Exception\ContainerException
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

        if (is_array($processor)) {
            if (!isset($processor['name'])) {
                throw new RuntimeException('Cannot create logger processor');
            }

            $processorClassName = $processor['name'];

            if (!class_exists($processorClassName)) {
                throw new RuntimeException('Cannot create logger processor (' . $processorClassName . ')');
            }

            $arguments = array_key_exists('args', $processor) ? $processor['args'] : array();

            if (!is_array($arguments)) {
                throw new RuntimeException('Arguments of processor (' . $processorClassName . ') must be array');
            }

            try {
                $instance = $this->createInstanceFromArguments($processorClassName, $arguments);
            } catch (\InvalidArgumentException $exception) {
                throw new RuntimeException(sprintf(
                    'Processor(%s) has an invalid argument configuration',
                    $processorClassName
                ), 0, $exception);
            }

            if (is_callable($instance)) {
                return $instance;
            }
        }

        throw new RuntimeException(
            'Unknown processor type, must be a Closure, array or the FQCN of an invokable class'
        );
    }

    /**
     * Handles the constructor arguments and if they're named, just sort them to fit constructor ordering.
     *
     * @param string $className
     * @param array  $arguments
     *
     * @return object
     * @throws \InvalidArgumentException If given arguments are not valid for provided className constructor.
     */
    private function createInstanceFromArguments($className, array $arguments)
    {
        $reflection = new \ReflectionClass($className);
        $constructor = $reflection->getConstructor();

        // There is no or at least a non-accessible constructor for provided class name,
        // therefore there is no need to handle arguments anyway
        if ($constructor === null) {
            return $reflection->newInstanceArgs($arguments);
        }

        if (!$constructor->isPublic()) {
            throw new \InvalidArgumentException(sprintf(
                '%s::__construct is not accessible',
                $className
            ));
        }

        $requiredArgsCount = $constructor->getNumberOfRequiredParameters();
        $argumentCount = count($arguments);

        if ($requiredArgsCount > $argumentCount) {
            throw new \InvalidArgumentException(sprintf(
                '%s::__construct() requires at least %d arguments; %d given',
                $className,
                $requiredArgsCount,
                $argumentCount
            ));
        }

        // Arguments supposed to be ordered
        if (isset($arguments[0])) {
            return $reflection->newInstanceArgs($arguments);
        }

        $parameters = array();

        foreach ($constructor->getParameters() as $parameter) {
            $parameterName = $parameter->getName();

            if (array_key_exists($parameterName, $arguments)) {
                $parameters[$parameter->getPosition()] = $arguments[$parameterName];
                continue;
            }

            if (!$parameter->isOptional()) {
                throw new \InvalidArgumentException(sprintf(
                    'Missing at least one required parameters `%s`',
                    $parameterName
                ));
            }

            $parameters[$parameter->getPosition()] = $parameter->getDefaultValue();
        }

        return $reflection->newInstanceArgs($parameters);
    }
}
