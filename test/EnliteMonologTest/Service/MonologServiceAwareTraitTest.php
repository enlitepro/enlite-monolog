<?php

namespace EnliteMonologTest\Service;

use EnliteMonolog\Service\MonologServiceAwareTrait;
use Monolog\Logger;
use Zend\ServiceManager\ServiceManager;

/**
 * @requires PHP 5.4
 * @covers \EnliteMonolog\Service\MonologServiceAwareTrait
 */
class MonologServiceAwareTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testSetMonologService()
    {
        /** @var MonologServiceAwareTrait $trait */
        $trait = $this->getMockForTrait('\EnliteMonolog\Service\MonologServiceAwareTrait');

        $logger = new Logger(__METHOD__);

        $trait->setMonologService($logger);

        self::assertSame($logger, $trait->getMonologService());
    }

    public function testGetMonologServiceViaServiceLocatorAwareInterface()
    {
        if (!\interface_exists('\Zend\ServiceManager\ServiceLocatorAwareInterface')) {
            self::markTestSkipped('\Zend\ServiceManager\ServiceLocatorAwareInterface is required.');
        }

        $sut = new TraitMock();

        $logger = new Logger(__METHOD__);

        $services = new ServiceManager();
        $services->setService('EnliteMonologService', $logger);

        $sut->setServiceLocator($services);

        self::assertSame($logger, $sut->getMonologService());
    }

    public function testGetMonologServiceViaServiceLocatorAwareTrait()
    {
        if (!\trait_exists('\Zend\ServiceManager\ServiceLocatorAwareTrait')) {
            self::markTestSkipped('\Zend\ServiceManager\ServiceLocatorAwareTrait is required.');
        }

        $sut = new TraitMock2();

        $logger = new Logger(__METHOD__);

        $services = new ServiceManager();
        $services->setService('EnliteMonologService', $logger);

        $sut->setServiceLocator($services);

        self::assertSame($logger, $sut->getMonologService());
    }

    public function testGetMonologServiceViaServiceLocatorMethod()
    {
        $sut = new TraitMock3();

        $logger = new Logger(__METHOD__);

        $services = new ServiceManager();
        $services->setService('EnliteMonologService', $logger);

        $sut->setServiceLocator($services);

        self::assertSame($logger, $sut->getMonologService());
    }

    public function testGetMonologServiceViaServiceLocatorProperty()
    {
        $sut = new TraitMock4();

        $logger = new Logger(__METHOD__);

        $services = new ServiceManager();
        $services->setService('EnliteMonologService', $logger);

        $sut->setServiceLocator($services);

        self::assertSame($logger, $sut->getMonologService());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testNotGetMonologService()
    {
        $sut = new TraitMock5();

        $logger = new Logger(__METHOD__);

        $services = new ServiceManager();
        $services->setService('EnliteMonologService', $logger);

        $sut->setServiceLocator($services);

        $sut->getMonologService();
    }
}
