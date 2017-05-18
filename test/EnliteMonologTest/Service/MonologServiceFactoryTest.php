<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonologTest\Service;


use EnliteMonolog\Service\MonologOptions;
use EnliteMonolog\Service\MonologServiceFactory;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\NullHandler;
use Monolog\Handler\TestHandler;
use Monolog\Processor\MemoryUsageProcessor;
use Zend\ServiceManager\ServiceManager;

class MonologServiceFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testCreateService()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => TestHandler::class)));

        $serviceManager = new ServiceManager();
        $serviceManager->setService('EnliteMonologOptions', new MonologOptions($config));

        $factory = new MonologServiceFactory();

        $service = $factory->createService($serviceManager);
        $this->assertInstanceOf('Monolog\Logger', $service);
        $this->assertEquals('test', $service->getName());

        $this->assertInstanceOf(TestHandler::class, $service->popHandler());
    }

    public function testCreateHandlerFromServiceLocator()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => TestHandler::class)));

        $serviceManager = new ServiceManager();
        $serviceManager->setService('TestHandler', 'works');

        $factory = new MonologServiceFactory();


        $this->assertEquals('works', $factory->createHandler($serviceManager, new MonologOptions($config), 'TestHandler'));
    }

    public function testCreateHandlerByClassNameWithoutArgs()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => TestHandler::class)));

        $serviceManager = new ServiceManager();
        $handler = array('name' => TestHandler::class);

        $factory = new MonologServiceFactory();

        $this->assertInstanceOf(TestHandler::class, $factory->createHandler($serviceManager, new MonologOptions($config), $handler));
    }

    public function testCreateHandlerByClassNameWithArgs()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => TestHandler::class)));

        $serviceManager = new ServiceManager();

        $handler = array('name' => HandlerMock::class, 'args' => array('some.log'));

        $factory = new MonologServiceFactory();

        $logger = $factory->createHandler($serviceManager, new MonologOptions($config), $handler);
        $this->assertInstanceOf(HandlerMock::class, $logger);
        $this->assertEquals('some.log', $logger->getPath());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCreateHandlerByEmptyClassname()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => TestHandler::class)));

        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $factory->createHandler($serviceManager, new MonologOptions($config), array());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCreateHandlerNotExistsClassname()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => TestHandler::class)));

        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $factory->createHandler($serviceManager, new MonologOptions($config), array('name' => 'unknown_class_name'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCreateHandlerWithBadArgs()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => TestHandler::class)));

        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $factory->createHandler($serviceManager, new MonologOptions($config), array('name' => TestHandler::class, 'args' => ''));
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

        $actual = $factory->createProcessor($serviceManager, MemoryUsageProcessor::class);

        self::assertInstanceOf(MemoryUsageProcessor::class, $actual);
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
        $serviceManager->setService(
            'MyFormatter',
            $expected = $this->getMockBuilder(\Monolog\Formatter\FormatterInterface::class)->getMock()
        );

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
            'name' => LineFormatter::class,
            'args' => 'MyArgs',
        ));
    }

    public function testCreateFormatterWithArguments()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $actual = $factory->createFormatter($serviceManager, array(
            'name' => LineFormatter::class,
            'args' => array(
                'format' => LineFormatter::SIMPLE_FORMAT,
                'dateFormat' => 'Y-m-d H:i:s',
            ),
        ));

        self::assertInstanceOf(LineFormatter::class, $actual);
    }

    public function testCreateFormatterWithoutArguments()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $actual = $factory->createFormatter($serviceManager, array(
            'name' => LineFormatter::class,
        ));

        self::assertInstanceOf(LineFormatter::class, $actual);
    }

    public function testCreateFormatterWithNamedArguments()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $dateFormat = 'Y-m-d\TH:i:sZ';
        $actual = $factory->createFormatter($serviceManager, array(
            'name' => LineFormatter::class,
            'args' => array(
                'dateFormat' => $dateFormat,
            ),
        ));

        self::assertInstanceOf(LineFormatter::class, $actual);
        $reflection = new \ReflectionClass($actual);
        $property = $reflection->getProperty('dateFormat');
        $property->setAccessible(true);
        self::assertSame($dateFormat, $property->getValue($actual), 'Unable to set arguments by name');
    }


    public function testCreateLoggerWithProcessor()
    {
        $options = new MonologOptions();
        $options->setProcessors(array(
            MemoryUsageProcessor::class,
        ));

        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $actual = $factory->createLogger($serviceManager, $options);

        self::assertInstanceOf('\Monolog\Logger', $actual);
        self::assertInstanceOf(MemoryUsageProcessor::class, $actual->popProcessor());
    }

    public function testCreateHandlerWithFormatter()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $actual = $factory->createHandler($serviceManager, new MonologOptions(), array(
            'name' => NullHandler::class,
            'formatter' => array(
                'name' => LineFormatter::class,
            ),
        ));

        self::assertInstanceOf(NullHandler::class, $actual);
        self::assertInstanceOf(LineFormatter::class, $actual->getFormatter());
    }

    public function testCreateHandlerWithRandomOrderArgs()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => \Monolog\Handler\TestHandler::class)));

        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        /** @var TestHandler $handler */
        $handler = $factory->createHandler($serviceManager, new MonologOptions($config), array(
            'name' => \Monolog\Handler\TestHandler::class,
            'args' => array(
                'bubble' => false
            )
        ));

        self::assertFalse($handler->getBubble());
    }


    public function testHandlersOrder()
    {
        $config = array(
            'name' => 'test',
            'handlers' => array(
                'testHandler' => 'TestHandler',
                'nullHandler' => array('name' => NullHandler::class),
            )
        );

        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        /** @var TestHandler $handler */
        $handler = $factory->createHandler($serviceManager, new MonologOptions($config), array(
            'name' => \Monolog\Handler\TestHandler::class,
            'args' => array(
                'bubble' => false
            )
        ));
        $serviceManager->setService('TestHandler', $handler);
        $serviceManager->setService('EnliteMonologOptions', new MonologOptions($config));

        $service = $factory->createService($serviceManager);

        $service->addError('HandleThis!');

        $handler1 = $service->popHandler();
        $this->assertInstanceOf(\Monolog\Handler\TestHandler::class, $handler1);

        $handler2 = $service->popHandler();
        $this->assertInstanceOf(NullHandler::class, $handler2);

        self::assertTrue($handler->hasErrorRecords());
    }

    /**
     * @return FormatterInterface
     */
    public function testHandlerGetsDefaultFormatter()
    {
        $serviceManager = new ServiceManager();

        $monologOptions = new MonologOptions();

        $factory = new MonologServiceFactory();
        $handler = $factory->createHandler($serviceManager, $monologOptions, array(
            'name' => NullHandler::class,
        ));

        $formatter = $handler->getFormatter();

        self::assertInstanceOf(\Monolog\Formatter\FormatterInterface::class, $formatter);

        return $formatter;
    }

    public function testHandlerGetsDefaultFormatterWithDefaultDateFormat()
    {
        $formatter = $this->testHandlerGetsDefaultFormatter();

        $line = $formatter->format(array(
            'datetime' => new \DateTime('2016-01-01 00:00:00', timezone_open('UTC')),
            'channel' => 'test',
            'level_name' => 'ERROR',
            'message' => 'foobar',
            'extra' => array(),
            'context' => array(),
        ));

        self::assertContains('[2016-01-01 00:00:00]', $line);
    }
}
