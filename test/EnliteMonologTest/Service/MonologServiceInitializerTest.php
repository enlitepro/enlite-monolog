<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonologTest\Service;

use EnliteMonolog\Service\MonologServiceAwareInterface;
use EnliteMonolog\Service\MonologServiceInitializer;
use Monolog\Logger;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

/**
 * @covers \EnliteMonolog\Service\MonologServiceInitializer
 */
class MonologServiceInitializerTest extends \PHPUnit_Framework_TestCase
{
    public function testInitialize()
    {
        $configArray = array(
            'invokables' => array(
                'test' => 'EnliteMonologTest\Service\ServiceMock',
            ),
            'factories' => array(
                'EnliteMonologService' => function () {
                    return new Logger('abc');
                }
            ),
            'initializers' => array(
                'EnliteMonolog\Service\MonologServiceInitializer'
            )
        );

        if ($this->isZF2()) {
            $serviceManager = new ServiceManager(new Config($configArray));
        } else { //ZF3
            $serviceManager = new ServiceManager($configArray);
        }

        /** @var MonologServiceAwareInterface $service */
        $service = $serviceManager->get('test');
        $this->assertInstanceOf('Monolog\Logger', $service->getMonologService());
    }

    private function isZF2()
    {
        return class_exists('\Zend\Stdlib\CallbackHandler');
    }

    public function testInitializeViaServiceLocator()
    {
        $service = new ServiceMock();

        self::assertNull($service->getMonologService());

        $logger = new Logger(__METHOD__);

        $services = new ServiceManager();
        $services->setService('EnliteMonologService', $logger);

        $sut = new MonologServiceInitializer();

        self::assertNull($sut->initialize($service, $services));

        self::assertSame($logger, $service->getMonologService());
    }

    public function testInitializeInvalidInstanceViaServiceLocator()
    {
        $service = new \stdClass();

        $services = new ServiceManager();

        $sut = new MonologServiceInitializer();

        self::assertNull($sut->initialize($service, $services));
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
