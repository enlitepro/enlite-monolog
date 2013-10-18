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
                $this->analyseService = $this->getServiceLocator()->get('EnliteMonologService');
            } else {
                if (property_exists($this, 'serviceLocator')
                    && $this->monologService instanceof ServiceLocatorInterface
                ) {
                    $this->analyseService = $this->serviceLocator->get('EnliteMonologService');
                } else {
                    throw new RuntimeException('Service locator not found');
                }
            }
        }
        return $this->monologService;
    }

}