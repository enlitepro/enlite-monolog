<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonologTest\Service;

use EnliteMonolog\Service\MonologServiceAbstractFactory;
use Zend\ServiceManager\ServiceManager;

/**
 * @covers \EnliteMonolog\Service\MonologServiceAbstractFactory
 */
class MonologServiceAbstractFactoryTest extends \PHPUnit_Framework_TestCase
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

    public function testCanCreateServiceWithName()
    {
        $factory = new MonologServiceAbstractFactory();
        $serviceLocator = new ServiceManager();
        $serviceLocator->setService(
            'config',
            array(
                 'EnliteMonolog' => array(
                     'default' => array()
                 )
            )
        );
        $this->assertTrue($factory->canCreateServiceWithName($serviceLocator, 'default', 'default'));
        $this->assertFalse($factory->canCreateServiceWithName($serviceLocator, 'test', 'test'));
    }

    public function testCreateServiceWithName()
    {
        $factory = new MonologServiceAbstractFactory();
        $serviceLocator = new ServiceManager();
        $serviceLocator->setService(
            'config',
            array(
                 'EnliteMonolog' => array(
                     'default' => array()
                 )
            )
        );

        $logger = $factory->createServiceWithName($serviceLocator, 'default', 'default');
        
        self::assertInstanceOf('\Monolog\Logger', $logger);
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

        self::assertTrue($sut->canCreate(new ContainerMock($services), 'default'));
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

        $logger = $sut(new ContainerMock($services), 'default');

        self::assertInstanceOf('\Monolog\Logger', $logger);
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

        self::assertInstanceOf('\Monolog\Logger', $logger);
    }
}
