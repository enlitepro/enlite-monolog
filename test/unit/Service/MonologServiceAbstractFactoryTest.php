<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonologTest\Service;

use EnliteMonolog\Service\MonologServiceAbstractFactory;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;

/**
 * @covers \EnliteMonolog\Service\MonologServiceAbstractFactory
 */
class MonologServiceAbstractFactoryTest extends TestCase
{

    public function testGetConfigWithNoneConfig()
    {
        $factory = new MonologServiceAbstractFactory();
        $serviceLocator = new ServiceManager();
        $serviceLocator->setService('config', array());

        $this->assertEquals(array(), $factory->getConfig($serviceLocator));
    }

    public function testGetConfigWithConfig()
    {
        $factory = new MonologServiceAbstractFactory();
        $serviceLocator = new ServiceManager();
        $serviceLocator->setService(
            'config',
            array(
                 'EnliteMonolog' => array(
                    'EnliteMonolog' => array()
                 )
            )
        );

        $this->assertEquals(array('EnliteMonolog' => array()), $factory->getConfig($serviceLocator));
    }

    public function testGetConfigWithAlreadyFetchConfig()
    {
        $factory = new MonologServiceAbstractFactory();
        $factory->setConfig(array('a' => 'b'));
        $serviceLocator = new ServiceManager();


        $this->assertEquals(array('a' => 'b'), $factory->getConfig($serviceLocator));
    }

    public function testCanCreate()
    {
        $services = new ServiceManager();

        $sut = new MonologServiceAbstractFactory();

        $services->setService(
            'config',
            array(
                'EnliteMonolog' => array(
                    'default' => array()
                )
            )
        );

        self::assertTrue($sut->canCreate($services, 'default'));
        self::assertFalse($sut->canCreate($services, 'test'));
    }

    public function testInvoke()
    {
        $services = new ServiceManager();

        $sut = new MonologServiceAbstractFactory();

        $services->setService(
            'config',
            array(
                'EnliteMonolog' => array(
                    'default' => array()
                )
            )
        );

        $logger = $sut($services, 'default');

        self::assertInstanceOf(Logger::class, $logger);
    }

    public function testCreateServiceFromServiceManager()
    {
        $services = new ServiceManager();
        $services->addAbstractFactory(new MonologServiceAbstractFactory());
        $services->setService('config', array(
            'EnliteMonolog' => array(
                'FooBar' => array(
                    'name' => 'FooBar',
                ),
            ),
        ));

        $logger = $services->get('FooBar');

        self::assertInstanceOf(Logger::class, $logger);
    }
}
