<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonolog\Service;

use Monolog\Logger;
use RuntimeException;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

trait MonologServiceAwareTrait
{

    /**
     * @var Logger
     */
    protected $monologService;

    /**
     * @var string
     */
    protected $monologLoggerName = 'EnliteMonologService';

    /**
     * @param Logger $monologService
     */
    public function setMonologService(Logger $monologService)
    {
        $this->monologService = $monologService;
    }

    /**
     * @throws \RuntimeException
     * @return Logger
     */
    public function getMonologService()
    {
        if (null === $this->monologService) {
            $services = null;
            if ($this instanceof ServiceLocatorAwareInterface
                || method_exists($this, 'getServiceLocator')
            ) {
                $services = $this->getServiceLocator();
            } elseif (property_exists($this, 'serviceLocator')) {
                $services = $this->serviceLocator;
            }

            if (!$services instanceof ServiceLocatorInterface) {
                throw new RuntimeException('Service locator not found');
            }

            // TODO Assert type is \Monolog\Logger.
            $this->monologService = $services->get($this->monologLoggerName);
        }

        return $this->monologService;
    }
}
