<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonologTest\Service;

use EnliteMonolog\Service\MonologServiceAbstractFactory;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\ServiceManager;
use Monolog\Logger;

/**
 * @covers \EnliteMonolog\Service\MonologServiceAbstractFactory
 */
class MonologServiceAbstractFactoryTest extends TestCase
{

    public function testGetConfigWithNoneConfig(): void
    {
        $factory = new MonologServiceAbstractFactory();
        $serviceLocator = new ServiceManager();
        $serviceLocator->setService('config', []);

        $this->assertEquals([], $factory->getConfig($serviceLocator));
    }

    public function testGetConfigWithConfig(): void
    {
        $factory = new MonologServiceAbstractFactory();
        $serviceLocator = new ServiceManager();
        $serviceLocator->setService(
            'config',
            [
                'EnliteMonolog' => [
                    'EnliteMonolog' => [],
                ],
            ]
        );

        $this->assertEquals(['EnliteMonolog' => []], $factory->getConfig($serviceLocator));
    }

    public function testGetConfigWithAlreadyFetchConfig(): void
    {
        $factory = new MonologServiceAbstractFactory();
        $factory->setConfig(['a' => 'b']);
        $serviceLocator = new ServiceManager();


        $this->assertEquals(['a' => 'b'], $factory->getConfig($serviceLocator));
    }

    public function testCanCreateServiceWithName(): void
    {
        $factory = new MonologServiceAbstractFactory();
        $serviceLocator = new ServiceManager();
        $serviceLocator->setService(
            'config',
            [
                'EnliteMonolog' => [
                    'default' => [],
                ],
            ]
        );
        $this->assertTrue($factory->canCreate($serviceLocator, 'default', 'default'));
        $this->assertFalse($factory->canCreate($serviceLocator, 'test', 'test'));
    }

    public function testCreateServiceWithName(): void
    {
        $factory = new MonologServiceAbstractFactory();
        $serviceLocator = new ServiceManager();
        $serviceLocator->setService(
            'config',
            [
                'EnliteMonolog' => [
                    'default' => [],
                ],
            ]
        );

        $logger = $factory->__invoke($serviceLocator, 'default');

        self::assertInstanceOf(Logger::class, $logger);
    }

    public function testCanCreate(): void
    {
        $services = new ServiceManager();

        $sut = new MonologServiceAbstractFactory();

        $services->setService(
            'config',
            [
                'EnliteMonolog' => [
                    'default' => [],
                ],
            ]
        );

        self::assertTrue($sut->canCreate(new ContainerMock($services), 'default'));
    }

    public function testInvoke(): void
    {
        $services = new ServiceManager();

        $sut = new MonologServiceAbstractFactory();

        $services->setService(
            'config',
            [
                'EnliteMonolog' => [
                    'default' => [],
                ],
            ]
        );

        $logger = $sut(new ContainerMock($services), 'default');

        self::assertInstanceOf(Logger::class, $logger);
    }

    public function testCreateServiceFromServiceManager(): void
    {
        $services = new ServiceManager();
        $services->addAbstractFactory(new MonologServiceAbstractFactory());
        $services->setService('config', [
            'EnliteMonolog' => [
                'FooBar' => [
                    'name' => 'FooBar',
                ],
            ],
        ]);

        $logger = $services->get('FooBar');

        self::assertInstanceOf(Logger::class, $logger);
    }
}
