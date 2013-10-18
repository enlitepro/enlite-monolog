<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonologTest\Service;

use EnliteMonolog\Service\MonologOptionsFactory;
use Zend\ServiceManager\ServiceManager;

class MonologOptionsFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testCreateService()
    {
        $serviceManager = new ServiceManager();
        $config = array(
            'EnliteMonolog' => array(
                'name' => 'test',
                'handlers' => array()
            )
        );
        $serviceManager->setService('config', $config);

        $factory = new MonologOptionsFactory();

        $service = $factory->createService($serviceManager);
        $this->assertInstanceOf('EnliteMonolog\Service\MonologOptions', $service);
        $this->assertEquals('test', $service->getName());
    }

}
