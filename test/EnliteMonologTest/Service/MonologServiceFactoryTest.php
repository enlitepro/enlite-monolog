<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonologTest\Service;


use EnliteMonolog\Service\MonologOptions;
use EnliteMonolog\Service\MonologServiceFactory;
use Monolog\Formatter\LineFormatter;
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

    public function testCreateProcessorFromAnonymousFunction()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $actual = $factory->createProcessor($serviceManager, $expected = function () {

        });

        self::assertSame($expected, $actual);
    }

    public function testCreateProcessorFromServiceName()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('MyProcessor', $expected = function () {

        });

        $factory = new MonologServiceFactory();

        $actual = $factory->createProcessor($serviceManager, 'MyProcessor');

        self::assertSame($expected, $actual);
    }

    public function testCreateProcessorFromClassName()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $actual = $factory->createProcessor($serviceManager, '\Monolog\Processor\MemoryUsageProcessor');

        self::assertInstanceOf('\Monolog\Processor\MemoryUsageProcessor', $actual);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCreateProcessorNotExistsClassName()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $factory->createProcessor($serviceManager, '\stdClass');
    }

    public function testCreateFormatterFromServiceName()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('MyFormatter', $expected = $this->getMock('\Monolog\Formatter\FormatterInterface'));

        $factory = new MonologServiceFactory();

        $actual = $factory->createFormatter($serviceManager, 'MyFormatter');

        self::assertSame($expected, $actual);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCreateFormatterWithMissingFormatterNameConfig()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $factory->createFormatter($serviceManager, array(

        ));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCreateFormatterNotExistsClassName()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $factory->createFormatter($serviceManager, array(
            'name' => '\InvalidFormatterClassName',
        ));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCreateFormatterWithInvalidFormatterArgumentConfig()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $factory->createFormatter($serviceManager, array(
            'name' => '\Monolog\Formatter\LineFormatter',
            'args' => 'MyArgs',
        ));
    }

    public function testCreateFormatterWithArguments()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $actual = $factory->createFormatter($serviceManager, array(
            'name' => '\Monolog\Formatter\LineFormatter',
            'args' => array(
                'format' => LineFormatter::SIMPLE_FORMAT,
                'dateFormat' => 'Y-m-d H:i:s',
            ),
        ));

        self::assertInstanceOf('\Monolog\Formatter\LineFormatter', $actual);
    }

    public function testCreateFormatterWithoutArguments()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $actual = $factory->createFormatter($serviceManager, array(
            'name' => '\Monolog\Formatter\LineFormatter',
        ));

        self::assertInstanceOf('\Monolog\Formatter\LineFormatter', $actual);
    }

    public function testCreateLoggerWithProcessor()
    {
        $options = new MonologOptions();
        $options->setProcessors(array(
            '\Monolog\Processor\MemoryUsageProcessor',
        ));

        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $actual = $factory->createLogger($serviceManager, $options);

        self::assertInstanceOf('\Monolog\Logger', $actual);
        self::assertInstanceOf('\Monolog\Processor\MemoryUsageProcessor', $actual->popProcessor());
    }

    public function testCreateHandlerWithFormatter()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $actual = $factory->createHandler($serviceManager, new MonologOptions(), array(
            'name' => '\Monolog\Handler\NullHandler',
            'formatter' => array(
                'name' => '\Monolog\Formatter\LineFormatter',
            ),
        ));

        self::assertInstanceOf('\Monolog\Handler\NullHandler', $actual);
        self::assertInstanceOf('\Monolog\Formatter\LineFormatter', $actual->getFormatter());
    }

    public function testCreateHandlerWithRandomOrderArgs()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => 'Monolog\Handler\TestHandler')));

        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        /** @var \Monolog\Handler\TestHandler $handler */
        $handler = $factory->createHandler($serviceManager, new MonologOptions($config), array(
            'name' => 'Monolog\Handler\TestHandler',
            'args' => array(
                'bubble' => false
            )
        ));

        self::assertFalse($handler->getBubble());
    }
}
