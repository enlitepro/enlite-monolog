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
            if ($this instanceof ServiceLocatorAwareInterface || method_exists($this, 'getServiceLocator')) {
                $this->monologService = $this->getServiceLocator()->get($this->monologLoggerName);
            } else {
                if (property_exists($this, 'serviceLocator')
                    && $this->serviceLocator instanceof ServiceLocatorInterface
                ) {
                    $this->monologService = $this->serviceLocator->get($this->monologLoggerName);
                } else {
                    throw new RuntimeException('Service locator not found');
                }
            }
        }
        return $this->monologService;
    }
}
