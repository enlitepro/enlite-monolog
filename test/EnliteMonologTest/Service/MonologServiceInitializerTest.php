<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonologTest\Service;


use EnliteMonolog\Service\MonologServiceAwareInterface;
use Monolog\Logger;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

class MonologServiceInitializerTest extends \PHPUnit_Framework_TestCase
{

    public function testInitialize()
    {
        $serviceManager = new ServiceManager(
            new Config(
                array(
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
                )
            )
        );

        /** @var MonologServiceAwareInterface $service */
        $service = $serviceManager->get('test');
        $this->assertInstanceOf('Monolog\Logger', $service->getMonologService());
    }

}
