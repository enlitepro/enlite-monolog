<?php

namespace EnliteMonologTest\Service;

use EnliteMonolog\Service\MonologServiceAwareTrait;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\ServiceManager;

/**
 * @requires PHP 5.4
 * @covers \EnliteMonolog\Service\MonologServiceAwareTrait
 */
class MonologServiceAwareTraitTest extends TestCase
{
    public function testSetMonologService(): void
    {
        /** @var MonologServiceAwareTrait $trait */
        $trait = $this->getMockForTrait('\EnliteMonolog\Service\MonologServiceAwareTrait');

        $logger = new Logger(__METHOD__);

        $trait->setMonologService($logger);

        self::assertSame($logger, $trait->getMonologService());
    }

    public function testGetMonologServiceViaServiceLocatorAwareInterface(): void
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

    public function testGetMonologServiceViaServiceLocatorAwareTrait(): void
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

    public function testGetMonologServiceViaServiceLocatorMethod(): void
    {
        $sut = new TraitMock3();

        $logger = new Logger(__METHOD__);

        $services = new ServiceManager();
        $services->setService('EnliteMonologService', $logger);

        $sut->setServiceLocator($services);

        self::assertSame($logger, $sut->getMonologService());
    }

    public function testGetMonologServiceViaServiceLocatorProperty(): void
    {
        $sut = new TraitMock4();

        $logger = new Logger(__METHOD__);

        $services = new ServiceManager();
        $services->setService('EnliteMonologService', $logger);

        $sut->setServiceLocator($services);

        self::assertSame($logger, $sut->getMonologService());
    }

    public function testNotGetMonologService(): void
    {
        $sut = new TraitMock5();

        $logger = new Logger(__METHOD__);

        $services = new ServiceManager();
        $services->setService('EnliteMonologService', $logger);

        $sut->setServiceLocator($services);

        $this->expectException(\RuntimeException::class);

        $sut->getMonologService();
    }
}
