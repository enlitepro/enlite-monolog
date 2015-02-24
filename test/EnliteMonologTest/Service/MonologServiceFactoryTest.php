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
        $config = array('name' => 'test', 'handlers' => array(array('name' => 'Monolog\Handler\TestHandler')));

        $serviceManager = new ServiceManager();
        $serviceManager->setService('TestHandler', 'works');

        $factory = new MonologServiceFactory();


        $this->assertEquals('works', $factory->createHandler($serviceManager, new MonologOptions($config), 'TestHandler'));
    }

    public function testCreateHandlerByClassNameWithoutArgs()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => 'Monolog\Handler\TestHandler')));

        $serviceManager = new ServiceManager();
        $handler = array('name' => 'Monolog\Handler\TestHandler');

        $factory = new MonologServiceFactory();

        $this->assertInstanceOf('Monolog\Handler\TestHandler', $factory->createHandler($serviceManager, new MonologOptions($config), $handler));
    }

    public function testCreateHandlerByClassNameWithArgs()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => 'Monolog\Handler\TestHandler')));

        $serviceManager = new ServiceManager();

        $handler = array('name' => 'EnliteMonologTest\Service\HandlerMock', 'args' => array('some.log'));

        $factory = new MonologServiceFactory();

        $logger = $factory->createHandler($serviceManager, new MonologOptions($config), $handler);
        $this->assertInstanceOf('EnliteMonologTest\Service\HandlerMock', $logger);
        $this->assertEquals('some.log', $logger->getPath());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCreateHandlerByEmptyClassname()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => 'Monolog\Handler\TestHandler')));

        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $factory->createHandler($serviceManager, new MonologOptions($config), array());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCreateHandlerNotExistsClassname()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => 'Monolog\Handler\TestHandler')));

        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $factory->createHandler($serviceManager, new MonologOptions($config), array('name' => 'unknown_class_name'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCreateHandlerWithBadArgs()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => 'Monolog\Handler\TestHandler')));

        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $factory->createHandler($serviceManager, new MonologOptions($config), array('name' => 'Monolog\Handler\TestHandler', 'args' => ''));
    }

}
