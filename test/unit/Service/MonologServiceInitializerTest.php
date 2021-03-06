<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonologTest\Service;

use EnliteMonolog\Service\MonologServiceAwareInterface;
use EnliteMonolog\Service\MonologServiceInitializer;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\ServiceManager;

/**
 * @covers \EnliteMonolog\Service\MonologServiceInitializer
 */
class MonologServiceInitializerTest extends TestCase
{
    public function testInitialize()
    {
        $configArray = array(
            'invokables' => array(
                'test' => ServiceMock::class,
            ),
            'factories' => array(
                'EnliteMonologService' => function () {
                    return new Logger('abc');
                }
            ),
            'initializers' => array(
                MonologServiceInitializer::class
            )
        );

        $serviceManager = new ServiceManager($configArray);

        /** @var MonologServiceAwareInterface $service */
        $service = $serviceManager->get('test');
        $this->assertInstanceOf(Logger::class, $service->getMonologService());
    }

    public function testInitializeViaServiceLocator()
    {
        $service = new ServiceMock();

        self::assertNull($service->getMonologService());

        $logger = new Logger(__METHOD__);

        $services = new ServiceManager();
        $services->setService('EnliteMonologService', $logger);

        $sut = new MonologServiceInitializer();

        self::assertNull($sut($services, $service));

        self::assertSame($logger, $service->getMonologService());
    }

    public function testInitializeInvalidInstanceViaServiceLocator()
    {
        $service = new \stdClass();

        $services = new ServiceManager();

        $sut = new MonologServiceInitializer();

        self::assertNull($sut($services, $service));
    }

    public function testInvoke()
    {
        $service = new ServiceMock();

        self::assertNull($service->getMonologService());

        $logger = new Logger(__METHOD__);

        $services = new ServiceManager();

        $services->setService('EnliteMonologService', $logger);

        $sut = new MonologServiceInitializer();

        self::assertNull($sut(new ContainerMock($services), $service));

        self::assertSame($logger, $service->getMonologService());
    }

    public function testInvokeInvalidInstance()
    {
        $service = new \stdClass();

        $services = new ServiceManager();

        $sut = new MonologServiceInitializer();

        self::assertNull($sut(new ContainerMock($services), $service));
    }
}
