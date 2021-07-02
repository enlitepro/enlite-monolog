<?php

namespace EnliteMonologTest\Service;

use EnliteMonolog\Service\MonologServiceAwareTrait;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;

/**
 * @requires PHP 5.4
 * @covers \EnliteMonolog\Service\MonologServiceAwareTrait
 */
class MonologServiceAwareTraitTest extends TestCase
{
    public function testSetMonologService()
    {
        /** @var MonologServiceAwareTrait $trait */
        $trait = $this->getMockForTrait(MonologServiceAwareTrait::class);

        $logger = new Logger(__METHOD__);

        $trait->setMonologService($logger);

        self::assertSame($logger, $trait->getMonologService());
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

    public function testNotGetMonologService()
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
