<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonologTest\Service;


use EnliteMonolog\Service\MonologOptions;
use EnliteMonolog\Service\MonologServiceFactory;
use Zend\ServiceManager\ServiceManager;

class MonologServiceFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testCreateService()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => 'Monolog\Handler\TestHandler')));

        $serviceManager = new ServiceManager();
        $serviceManager->setService('EnliteMonologOptions', new MonologOptions($config));

        $factory = new MonologServiceFactory();

        $service = $factory->createService($serviceManager);
        $this->assertInstanceOf('Monolog\Logger', $service);
        $this->assertEquals('test', $service->getName());

        $this->assertInstanceOf('Monolog\Handler\TestHandler', $service->popHandler());
    }

    public function testCreateHandlerFromServiceLocator()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('TestHandler', 'works');

        $factory = new MonologServiceFactory();

        $this->assertEquals('works', $factory->createHandler($serviceManager, 'TestHandler'));
    }

    public function testCreateHandlerByClassNameWithoutArgs()
    {
        $serviceManager = new ServiceManager();
        $handler = array('name' => 'Monolog\Handler\TestHandler');

        $factory = new MonologServiceFactory();

        $this->assertInstanceOf('Monolog\Handler\TestHandler', $factory->createHandler($serviceManager, $handler));
    }

    public function testCreateHandlerByClassNameWithArgs()
    {
        $serviceManager = new ServiceManager();

        $handler = array('name' => 'EnliteMonologTest\Service\HandlerMock', 'args' => array('some.log'));

        $factory = new MonologServiceFactory();

        $logger = $factory->createHandler($serviceManager, $handler);
        $this->assertInstanceOf('EnliteMonologTest\Service\HandlerMock', $logger);
        $this->assertEquals('some.log', $logger->getPath());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCreateHandlerByEmptyClassname()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $factory->createHandler($serviceManager, array());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCreateHandlerNotExistsClassname()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $factory->createHandler($serviceManager, array('name' => 'unknown_class_name'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCreateHandlerWithBadArgs()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $factory->createHandler($serviceManager, array('name' => 'Monolog\Handler\TestHandler', 'args' => ''));
    }

}
