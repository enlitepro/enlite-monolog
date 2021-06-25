<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonologTest\Service;

use DateTime;
use EnliteMonolog\Service\MonologOptions;
use EnliteMonolog\Service\MonologServiceFactory;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\NullHandler;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Monolog\Processor\MemoryUsageProcessor;
use PHPUnit\Framework\TestCase;
use stdClass;
use Laminas\ServiceManager\ServiceManager;

/**
 * @covers \EnliteMonolog\Service\MonologServiceFactory
 */
class MonologServiceFactoryTest extends TestCase
{

    public function testCreateService()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => TestHandler::class)));

        $serviceManager = new ServiceManager();
        $serviceManager->setService('EnliteMonologOptions', new MonologOptions($config));

        $factory = new MonologServiceFactory();

        $service = $factory->createService($serviceManager);
        $this->assertInstanceOf(Logger::class, $service);
        $this->assertEquals('test', $service->getName());

        $this->assertInstanceOf(TestHandler::class, $service->popHandler());
    }

    public function testCreateHandlerFromServiceLocator()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => TestHandler::class)));

        $serviceManager = new ServiceManager();
        $serviceManager->setService('TestHandler', 'works');

        $factory = new MonologServiceFactory();

        $handler = $factory->createHandler($serviceManager, new MonologOptions($config), 'TestHandler');
        $this->assertEquals('works', $handler);
    }

    public function testCreateHandlerByClassNameWithoutArgs()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => TestHandler::class)));

        $serviceManager = new ServiceManager();
        $handler = array('name' => TestHandler::class);

        $factory = new MonologServiceFactory();
    
        $actual = $factory->createHandler($serviceManager, new MonologOptions($config), $handler);
        $this->assertInstanceOf(TestHandler::class, $actual);
    }

    public function testCreateHandlerByClassNameWithArgs()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => TestHandler::class)));

        $serviceManager = new ServiceManager();

        $handler = array('name' => HandlerMock::class, 'args' => array('some.log'));

        $factory = new MonologServiceFactory();

        /** @var HandlerMock $logger */
        $logger = $factory->createHandler($serviceManager, new MonologOptions($config), $handler);
        $this->assertInstanceOf(HandlerMock::class, $logger);
        $this->assertEquals('some.log', $logger->getPath());
    }

    public function testCreateHandlerByEmptyClassname()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => TestHandler::class)));

        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        self::expectException(\RuntimeException::class);
        self::expectExceptionCode(0);

        $factory->createHandler($serviceManager, new MonologOptions($config), array());
    }

    public function testCreateHandlerNotExistsClassname()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => TestHandler::class)));

        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        self::expectException(\RuntimeException::class);
        self::expectExceptionCode(0);

        $factory->createHandler($serviceManager, new MonologOptions($config), array('name' => 'unknown_class_name'));
    }

    public function testCreateHandlerWithBadArgs()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => TestHandler::class)));

        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        self::expectException(\RuntimeException::class);
        self::expectExceptionCode(0);

        $factory->createHandler($serviceManager, new MonologOptions($config), array(
            'name' => TestHandler::class,
            'args' => ''
        ));
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

    public function testCreateProcessorNotExistsClassName()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        self::expectException(\RuntimeException::class);
        self::expectExceptionCode(0);
        self::expectExceptionMessage(
            'Unknown processor type, must be a Closure, array or the FQCN of an invokable class'
        );

        $factory->createProcessor($serviceManager, stdClass::class);
    }

    public function testCreateNonCallableProcessorFromServiceName()
    {
        $services = new ServiceManager();
        $services->setService(stdClass::class, new stdClass());

        $sut = new MonologServiceFactory();

        self::expectException(\RuntimeException::class);
        self::expectExceptionCode(0);
        self::expectExceptionMessage(
            'Unknown processor type, must be a Closure, array or the FQCN of an invokable class'
        );

        $sut->createProcessor($services, stdClass::class);
    }

    public function testCreateProcessorWithMissingProcessorNameConfig()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        self::expectException(\RuntimeException::class);
        self::expectExceptionCode(0);

        $factory->createProcessor($serviceManager, array(

        ));
    }

    public function testCreateProcessorNotExistsClassNameInNamePart()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        self::expectException(\RuntimeException::class);
        self::expectExceptionCode(0);

        $factory->createProcessor($serviceManager, array(
            'name' => '\InvalidProcessorClassName',
        ));
    }

    public function testCreateProcessorWithInvalidProcessorArgumentConfig()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        self::expectException(\RuntimeException::class);
        self::expectExceptionCode(0);

        $factory->createProcessor($serviceManager, array(
            'name' => ProcessorMock::class,
            'args' => 'MyArgs',
        ));
    }

    public function testCreateProcessorWithWrongNamedArguments()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        self::expectException(\RuntimeException::class);
        self::expectExceptionCode(0);

        $factory->createProcessor($serviceManager, array(
            'name' => ProcessorMock::class,
            'args' => array(
                'notExisted' => 'test',
            ),
        ));
    }

    public function testCreateProcessorWithArguments()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $actual = $factory->createProcessor($serviceManager, array(
            'name' => ProcessorMock::class,
            'args' => array(
                'test',
            ),
        ));

        self::assertInstanceOf(ProcessorMock::class, $actual);
    }

    public function testCreateProcessorWithoutArguments()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $actual = $factory->createProcessor($serviceManager, array(
            'name' => MemoryUsageProcessor::class,
        ));

        self::assertInstanceOf(MemoryUsageProcessor::class, $actual);
    }

    public function testCreateProcessorWithNamedArguments()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $argument = 'test';
        $actual = $factory->createProcessor($serviceManager, array(
            'name' => ProcessorMock::class,
            'args' => array(
                'argument' => $argument,
            ),
        ));

        self::assertInstanceOf(ProcessorMock::class, $actual);
        $reflection = new \ReflectionClass($actual);
        $property = $reflection->getProperty('argument');
        $property->setAccessible(true);
        self::assertSame($argument, $property->getValue($actual), 'Unable to set arguments by name');
    }

    public function testCreateFormatterFromServiceName()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'MyFormatter',
            $expected = $this->getMockBuilder(FormatterInterface::class)->getMock()
        );

        $factory = new MonologServiceFactory();

        $actual = $factory->createFormatter($serviceManager, 'MyFormatter');

        self::assertSame($expected, $actual);
    }

    public function testCreateFormatterWithMissingFormatterNameConfig()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        self::expectException(\RuntimeException::class);
        self::expectExceptionCode(0);

        $factory->createFormatter($serviceManager, array(

        ));
    }

    public function testCreateFormatterNotExistsClassName()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        self::expectException(\RuntimeException::class);
        self::expectExceptionCode(0);

        $factory->createFormatter($serviceManager, array(
            'name' => '\InvalidFormatterClassName',
        ));
    }

    public function testCreateFormatterWithInvalidFormatterArgumentConfig()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        self::expectException(\RuntimeException::class);
        self::expectExceptionCode(0);

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

        self::assertInstanceOf(Logger::class, $actual);
        self::assertInstanceOf(MemoryUsageProcessor::class, $actual->popProcessor());
    }

    public function testCannotCreateWithFormatterWithoutCorrectInterface()
    {
        if (!defined('Monolog\Logger::API') || \Monolog\Logger::API === 1) {
            $this->markTestSkipped('Not supported by Monolog v1');
        }

        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $this->expectException(\RuntimeException::class);

        $factory->createHandler($serviceManager, new MonologOptions(), array(
            'name' => NullHandler::class,
            'formatter' => array(
                'name' => LineFormatter::class,
            ),
        ));
    }

    public function testCreateHandlerWithFormatter()
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $actual = $factory->createHandler($serviceManager, new MonologOptions(), array(
            'name' => TestHandler::class,
            'formatter' => array(
                'name' => LineFormatter::class,
            ),
        ));

        self::assertInstanceOf(TestHandler::class, $actual);
        self::assertInstanceOf(LineFormatter::class, $actual->getFormatter());
    }

    public function testCreateHandlerWithRandomOrderArgs()
    {
        $config = array('name' => 'test', 'handlers' => array(array('name' => TestHandler::class)));

        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        /** @var TestHandler $handler */
        $handler = $factory->createHandler($serviceManager, new MonologOptions($config), array(
            'name' => TestHandler::class,
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
            'name' => TestHandler::class,
            'args' => array(
                'bubble' => false
            )
        ));
        $serviceManager->setService('TestHandler', $handler);
        $serviceManager->setService('EnliteMonologOptions', new MonologOptions($config));

        $service = $factory->createService($serviceManager);

        $service->error('HandleThis!');

        $handler1 = $service->popHandler();
        $this->assertInstanceOf(TestHandler::class, $handler1);

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
            'name' => TestHandler::class,
        ));

        $formatter = $handler->getFormatter();

        self::assertInstanceOf(FormatterInterface::class, $formatter);

        return $formatter;
    }

    public function testHandlerGetsDefaultFormatterWithDefaultDateFormat()
    {
        $formatter = $this->testHandlerGetsDefaultFormatter();

        $line = $formatter->format(array(
            'datetime' => new DateTime('2016-01-01 00:00:00', timezone_open('UTC')),
            'channel' => 'test',
            'level_name' => 'ERROR',
            'message' => 'foobar',
            'extra' => array(),
            'context' => array(),
        ));

        self::assertStringContainsString('2016-01-01', $line);
    }

    public function testInvoke()
    {
        $services = new ServiceManager();

        $config = array('name' => 'test', 'handlers' => array(array('name' => TestHandler::class)));

        $services->setService('EnliteMonologOptions', new MonologOptions($config));

        $sut = new MonologServiceFactory();

        $service = $sut(new ContainerMock($services), 'EnliteMonolog');
        $this->assertInstanceOf(Logger::class, $service);
        $this->assertEquals('test', $service->getName());

        $this->assertInstanceOf(TestHandler::class, $service->popHandler());
    }

    public function testCreateHandlerFromOptions()
    {
        $sut = new MonologServiceFactory();

        $services = new ServiceManager();

        $options = new MonologOptions();
        $options->setHandlers(array(
            'HandlerMock' => array(
                'name' => HandlerMock::class,
                'args' => array(
                    'path' => '/FooBar',
                ),
            ),
        ));

        $result = $sut->createHandler($services, $options, array(
            'name' => HandlerMock::class,
            'args' => array(
                'handler' => 'HandlerMock',
                'path' => '/FizBuz',
            ),
        ));

        self::assertInstanceOf(HandlerMock::class, $result);
    }

    public function testCreateHandlerWithInvalidArguments()
    {
        $sut = new MonologServiceFactory();

        $services = new ServiceManager();

        $options = new MonologOptions();

        self::expectException(\RuntimeException::class);
        self::expectExceptionCode(0);
        self::expectExceptionMessage(
            'Handler(EnliteMonologTest\Service\HandlerMock) has an invalid argument configuration'
        );

        $sut->createHandler($services, $options, array(
            'name' => HandlerMock::class,
            'args' => array(),
        ));
    }

    public function testCreateFormatterWithMissingArguments()
    {
        $sut = new MonologServiceFactory();

        $services = new ServiceManager();

        self::expectException(\RuntimeException::class);
        self::expectExceptionCode(0);
        self::expectExceptionMessage(
            'Formatter(EnliteMonologTest\Service\FormatterMock) has an invalid argument config'
        );

        $sut->createFormatter($services, array(
            'name' => FormatterMock::class,
            'args' => array(),
        ));
    }

    public function testCreateFormatterWithInvalidArguments()
    {
        $sut = new MonologServiceFactory();

        $services = new ServiceManager();

        self::expectException(\RuntimeException::class);
        self::expectExceptionCode(0);
        self::expectExceptionMessage(
            'Formatter(EnliteMonologTest\Service\FormatterMock) has an invalid argument config'
        );

        $sut->createFormatter($services, array(
            'name' => FormatterMock::class,
            'args' => array(
                'NotEncoder' => new \stdClass(),
            ),
        ));
    }

    public function testCreateFormatterWithPrivateConstructor()
    {
        $sut = new MonologServiceFactory();

        $services = new ServiceManager();

        self::expectException(\RuntimeException::class);
        self::expectExceptionCode(0);
        self::expectExceptionMessage(
            'Formatter(EnliteMonologTest\Service\FormatterPrivateConstructorMock) has an invalid'
        );

        $sut->createFormatter($services, array(
            'name' => FormatterPrivateConstructorMock::class,
            'args' => array(),
        ));
    }

    public function testCreateFormatterFromStdClass()
    {
        $sut = new MonologServiceFactory();

        $services = new ServiceManager();

        $result = $sut->createFormatter($services, array(
            'name' => stdClass::class,
            'args' => array(),
        ));

        self::assertInstanceOf(stdClass::class, $result);
    }
}
