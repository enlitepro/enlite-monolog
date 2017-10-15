<?php

namespace EnliteMonolog\Service;

use Monolog\ErrorHandler;
use Psr\Log\LoggerInterface;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\ModuleManager\ModuleEvent;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * TODO Require zendframework/zend-eventmanager:^2.2, extend AbstractListenerAggregate, and drop custom detach.
 */
class ErrorHandlerListener implements ListenerAggregateInterface
{
    /**
     * @var callable[]
     */
    private $listeners = array();

    /**
     * {@inheritDoc}
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $callback) {
            $events->detach($callback);
            unset($this->listeners[$index]);
        }
    }

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     * @param int $priority
     * @return void
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(ModuleEvent::EVENT_LOAD_MODULES_POST, array(
            $this,
            'registerErrorHandlers'
        ));
    }

    /**
     * @param EventInterface $event
     * @return ErrorHandler[]
     */
    public function registerErrorHandlers(EventInterface $event)
    {
        $services = $event->getParam('ServiceManager');
        if (!$services instanceof ServiceLocatorInterface) {
            return array();
        }

        $config = array();
        if ($services->has('config')) {
            $config = $services->get('config');
        } elseif ($event instanceof ModuleEvent) {
            $config = $event->getConfigListener()->getMergedConfig(false);
        }

        if (!is_array($config)) {
            return array();
        }

        if (!array_key_exists('EnliteMonologErrorHandlers', $config)) {
            return array();
        }

        $handlers = $config['EnliteMonologErrorHandlers'];
        if (!is_array($handlers) || count($handlers) < 1) {
            return array();
        }

        // Transform error-handler configurations into option objects.
        $handlers = array_map(function ($config) {
            return new ErrorHandlerOptions($config);
        }, $handlers);

        // Transform error-handler options into error-handler instances.
        return array_map(function (ErrorHandlerOptions $options) use ($services) {
            if (!$services->has($options->getLogger())) {
                return null;
            }

            $logger = $services->get($options->getLogger());

            if (!$logger instanceof LoggerInterface) {
                return null;
            }

            if (class_exists('\Monolog\ErrorHandler')) {
                return ErrorHandler::register(
                    $logger,
                    $options->getErrorLevelMap(),
                    $options->getExceptionLevel(),
                    $options->getFatalLevel()
                );
            }

            return null;
        }, $handlers);
    }
}
