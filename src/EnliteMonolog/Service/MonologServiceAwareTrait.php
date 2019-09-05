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

    public function setMonologService(Logger $monologService)
    {
        $this->monologService = $monologService;
    }

    /**
     * @throws \RuntimeException
     */
    public function getMonologService(): Logger
    {
        if (null === $this->monologService) {
            if (property_exists($this, 'serviceLocator')
                && $this->serviceLocator instanceof ServiceLocatorInterface
            ) {
                $this->monologService = $this->serviceLocator->get($this->monologLoggerName);
            } else {
                throw new RuntimeException('Service locator not found');
            }
        }
        return $this->monologService;
    }
}
