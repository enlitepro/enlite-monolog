<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonologTest\Service;

use EnliteMonolog\Service\MonologServiceAwareInterface;
use EnliteMonolog\Service\MonologServiceInitializer;
use Monolog\Logger;
use Monolog\Test\TestCase;
use stdClass;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;
use EnliteMonologTest\Service\ServiceMock;

/**
 * @covers \EnliteMonolog\Service\MonologServiceInitializer
 */
class MonologServiceInitializerTest extends TestCase
{
    public function testInitialize(): void
    {
        $configArray = [
            'invokables' => [
                'test' => ServiceMock::class,
            ],
            'factories' => [
                'EnliteMonologService' => function () {
                    return new Logger('abc');
                },
            ],
            'initializers' => [
                MonologServiceInitializer::class,
            ],
        ];

        $serviceManager = new ServiceManager($configArray);

        /** @var MonologServiceAwareInterface $service */
        $service = $serviceManager->get('test');
        $this->assertInstanceOf(Logger::class, $service->getMonologService());
    }

    public function testInitializeViaServiceLocator(): void
    {
        $service = new ServiceMock();

        self::assertNull($service->getMonologService());

        $logger = new Logger(__METHOD__);

        $services = new ServiceManager();
        $services->setService('EnliteMonologService', $logger);

        $sut = new MonologServiceInitializer();

        self::assertNull($sut->__invoke($services, $service));

        self::assertSame($logger, $service->getMonologService());
    }

    public function testInitializeInvalidInstanceViaServiceLocator(): void
    {
        $service = new stdClass();

        $services = new ServiceManager();

        $sut = new MonologServiceInitializer();

        self::assertNull($sut->__invoke($services, $service));
    }

    public function testInvoke(): void
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

    public function testInvokeInvalidInstance(): void
    {
        $service = new stdClass();

        $services = new ServiceManager();

        $sut = new MonologServiceInitializer();

        self::assertNull($sut(new ContainerMock($services), $service));
    }
}
