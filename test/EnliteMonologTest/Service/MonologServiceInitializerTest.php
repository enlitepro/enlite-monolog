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
}
