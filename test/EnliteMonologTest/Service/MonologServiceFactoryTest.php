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
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;
use stdClass;
use Zend\ServiceManager\ServiceManager;
use EnliteMonologTest\Service\FormatterPrivateConstructorMock;
use EnliteMonologTest\Service\FormatterMock;
use EnliteMonologTest\Service\HandlerMock;
use Monolog\Logger;
use Monolog\Processor\MemoryUsageProcessor;
use EnliteMonologTest\Service\ProcessorMock;

/**
 * @covers \EnliteMonolog\Service\MonologServiceFactory
 */
class MonologServiceFactoryTest extends TestCase
{

    public function testCreateService(): void
    {
        $config = ['name' => 'test', 'handlers' => [['name' => TestHandler::class]]];

        $serviceManager = new ServiceManager();
        $serviceManager->setService('EnliteMonologOptions', new MonologOptions($config));

        $factory = new MonologServiceFactory();

        $service = $factory->createService($serviceManager);
        $this->assertInstanceOf(Logger::class, $service);
        $this->assertEquals('test', $service->getName());

        $this->assertInstanceOf(TestHandler::class, $service->popHandler());
    }

    public function testCreateHandlerFromServiceLocator(): void
    {
        $config = ['name' => 'test', 'handlers' => [['name' => TestHandler::class]]];

        $serviceManager = new ServiceManager();
        $handlerService = new TestHandler();
        $serviceManager->setService('TestHandler', $handlerService);

        $factory = new MonologServiceFactory();

        $handler = $factory->createHandler($serviceManager, new MonologOptions($config), 'TestHandler');
        $this->assertSame($handlerService, $handler);
    }

    public function testCreateHandlerByClassNameWithoutArgs(): void
    {
        $config = ['name' => 'test', 'handlers' => [['name' => TestHandler::class]]];

        $serviceManager = new ServiceManager();
        $handler = ['name' => TestHandler::class];

        $factory = new MonologServiceFactory();

        $actual = $factory->createHandler($serviceManager, new MonologOptions($config), $handler);
        $this->assertInstanceOf(TestHandler::class, $actual);
    }

    public function testCreateHandlerByClassNameWithArgs(): void
    {
        $config = ['name' => 'test', 'handlers' => [['name' => TestHandler::class]]];

        $serviceManager = new ServiceManager();

        $handler = ['name' => HandlerMock::class, 'args' => ['some.log']];

        $factory = new MonologServiceFactory();

        /** @var HandlerMock $logger */
        $logger = $factory->createHandler($serviceManager, new MonologOptions($config), $handler);
        $this->assertInstanceOf(HandlerMock::class, $logger);
        $this->assertEquals('some.log', $logger->getPath());
    }

    public function testCreateHandlerByEmptyClassname(): void
    {
        $config = ['name' => 'test', 'handlers' => [['name' => TestHandler::class]]];

        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);

        $factory->createHandler($serviceManager, new MonologOptions($config), []);
    }

    public function testCreateHandlerNotExistsClassname(): void
    {
        $config = ['name' => 'test', 'handlers' => [['name' => TestHandler::class]]];

        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);

        $factory->createHandler($serviceManager, new MonologOptions($config), ['name' => 'unknown_class_name']);
    }

    public function testCreateHandlerWithBadArgs(): void
    {
        $config = ['name' => 'test', 'handlers' => [['name' => TestHandler::class]]];

        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);

        $factory->createHandler($serviceManager, new MonologOptions($config), [
            'name' => TestHandler::class,
            'args' => '',
        ]);
    }

    public function testCreateProcessorFromAnonymousFunction(): void
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $actual = $factory->createProcessor($serviceManager, $expected = function () {
        });

        self::assertSame($expected, $actual);
    }

    public function testCreateProcessorFromServiceName(): void
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('MyProcessor', $expected = function () {
        });

        $factory = new MonologServiceFactory();

        $actual = $factory->createProcessor($serviceManager, 'MyProcessor');

        self::assertSame($expected, $actual);
    }

    public function testCreateProcessorFromClassName(): void
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $actual = $factory->createProcessor($serviceManager, MemoryUsageProcessor::class);

        self::assertInstanceOf(MemoryUsageProcessor::class, $actual);
    }

    public function testCreateProcessorNotExistsClassName(): void
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'Unknown processor type, must be a Closure, array or the FQCN of an invokable class'
        );

        $factory->createProcessor($serviceManager, stdClass::class);
    }

    public function testCreateNonCallableProcessorFromServiceName(): void
    {
        $services = new ServiceManager();
        $services->setService(stdClass::class, new stdClass());

        $sut = new MonologServiceFactory();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'Unknown processor type, must be a Closure, array or the FQCN of an invokable class'
        );

        $sut->createProcessor($services, stdClass::class);
    }

    public function testCreateProcessorWithMissingProcessorNameConfig(): void
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);

        $factory->createProcessor($serviceManager, []);
    }

    public function testCreateProcessorNotExistsClassNameInNamePart(): void
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);

        $factory->createProcessor($serviceManager, [
            'name' => '\InvalidProcessorClassName',
        ]);
    }

    public function testCreateProcessorWithInvalidProcessorArgumentConfig(): void
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);

        $factory->createProcessor($serviceManager, [
            'name' => ProcessorMock::class,
            'args' => 'MyArgs',
        ]);
    }

    public function testCreateProcessorWithWrongNamedArguments(): void
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);

        $factory->createProcessor($serviceManager, [
            'name' => ProcessorMock::class,
            'args' => [
                'notExisted' => 'test',
            ],
        ]);
    }

    public function testCreateProcessorWithArguments(): void
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $actual = $factory->createProcessor($serviceManager, [
            'name' => ProcessorMock::class,
            'args' => [
                'test',
            ],
        ]);

        self::assertInstanceOf(ProcessorMock::class, $actual);
    }

    public function testCreateProcessorWithoutArguments(): void
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $actual = $factory->createProcessor($serviceManager, [
            'name' => MemoryUsageProcessor::class,
        ]);

        self::assertInstanceOf(MemoryUsageProcessor::class, $actual);
    }

    public function testCreateProcessorWithNamedArguments(): void
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $argument = 'test';
        $actual = $factory->createProcessor($serviceManager, [
            'name' => ProcessorMock::class,
            'args' => [
                'argument' => $argument,
            ],
        ]);

        self::assertInstanceOf(ProcessorMock::class, $actual);
        $reflection = new ReflectionClass($actual);
        $property = $reflection->getProperty('argument');
        $property->setAccessible(true);
        self::assertSame($argument, $property->getValue($actual), 'Unable to set arguments by name');
    }

    public function testCreateFormatterFromServiceName(): void
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

    public function testCreateFormatterWithMissingFormatterNameConfig(): void
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);

        $factory->createFormatter($serviceManager, []);
    }

    public function testCreateFormatterNotExistsClassName(): void
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);

        $factory->createFormatter($serviceManager, [
            'name' => '\InvalidFormatterClassName',
        ]);
    }

    public function testCreateFormatterWithInvalidFormatterArgumentConfig(): void
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);

        $factory->createFormatter($serviceManager, [
            'name' => LineFormatter::class,
            'args' => 'MyArgs',
        ]);
    }

    public function testCreateFormatterWithArguments(): void
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $actual = $factory->createFormatter($serviceManager, [
            'name' => LineFormatter::class,
            'args' => [
                'format' => LineFormatter::SIMPLE_FORMAT,
                'dateFormat' => 'Y-m-d H:i:s',
            ],
        ]);

        self::assertInstanceOf(LineFormatter::class, $actual);
    }

    public function testCreateFormatterWithoutArguments(): void
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $actual = $factory->createFormatter($serviceManager, [
            'name' => LineFormatter::class,
        ]);

        self::assertInstanceOf(LineFormatter::class, $actual);
    }

    public function testCreateFormatterWithNamedArguments(): void
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $dateFormat = 'Y-m-d\TH:i:sZ';
        $actual = $factory->createFormatter($serviceManager, [
            'name' => LineFormatter::class,
            'args' => [
                'dateFormat' => $dateFormat,
            ],
        ]);

        self::assertInstanceOf(LineFormatter::class, $actual);
        $reflection = new ReflectionClass($actual);
        $property = $reflection->getProperty('dateFormat');
        $property->setAccessible(true);
        self::assertSame($dateFormat, $property->getValue($actual), 'Unable to set arguments by name');
    }


    public function testCreateLoggerWithProcessor(): void
    {
        $options = new MonologOptions();
        $options->setProcessors([
            MemoryUsageProcessor::class,
        ]);

        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $actual = $factory->createLogger($serviceManager, $options);

        self::assertInstanceOf(Logger::class, $actual);
        self::assertInstanceOf(MemoryUsageProcessor::class, $actual->popProcessor());
    }

    public function testCreateHandlerWithFormatter(): void
    {
        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        $actual = $factory->createHandler($serviceManager, new MonologOptions(), [
            'name' => TestHandler::class,
            'formatter' => [
                'name' => LineFormatter::class,
            ],
        ]);

        self::assertInstanceOf(TestHandler::class, $actual);
        self::assertInstanceOf(LineFormatter::class, $actual->getFormatter());
    }

    public function testCreateHandlerWithRandomOrderArgs(): void
    {
        $config = ['name' => 'test', 'handlers' => [['name' => TestHandler::class]]];

        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        /** @var TestHandler $handler */
        $handler = $factory->createHandler($serviceManager, new MonologOptions($config), [
            'name' => TestHandler::class,
            'args' => [
                'bubble' => false,
            ],
        ]);

        self::assertFalse($handler->getBubble());
    }


    public function testHandlersOrder(): void
    {
        $config = [
            'name' => 'test',
            'handlers' => [
                'testHandler' => 'TestHandler',
                'nullHandler' => ['name' => TestHandler::class],
            ],
        ];

        $serviceManager = new ServiceManager();
        $factory = new MonologServiceFactory();

        /** @var TestHandler $handler */
        $handler = $factory->createHandler($serviceManager, new MonologOptions($config), [
            'name' => TestHandler::class,
            'args' => [
                'bubble' => false,
            ],
        ]);
        $serviceManager->setService('TestHandler', $handler);
        $serviceManager->setService('EnliteMonologOptions', new MonologOptions($config));

        $service = $factory->createService($serviceManager);

        $service->error('HandleThis!');

        $handler1 = $service->popHandler();
        $this->assertInstanceOf(TestHandler::class, $handler1);

        $handler2 = $service->popHandler();
        $this->assertInstanceOf(TestHandler::class, $handler2);

        self::assertTrue($handler->hasErrorRecords());
    }

    public function testHandlerGetsDefaultFormatter(): FormatterInterface
    {
        $serviceManager = new ServiceManager();

        $monologOptions = new MonologOptions();

        $factory = new MonologServiceFactory();
        $handler = $factory->createHandler($serviceManager, $monologOptions, [
            'name' => TestHandler::class,
        ]);

        $formatter = $handler->getFormatter();

        self::assertInstanceOf(FormatterInterface::class, $formatter);

        return $formatter;
    }

    public function testHandlerGetsDefaultFormatterWithDefaultDateFormat(): void
    {
        $formatter = $this->testHandlerGetsDefaultFormatter();

        $line = $formatter->format([
            'datetime' => new \DateTime('2016-01-01 00:00:00', timezone_open('UTC')),
            'channel' => 'test',
            'level_name' => 'ERROR',
            'message' => 'foobar',
            'extra' => [],
            'context' => [],
        ]);

        self::assertStringContainsString('[2016-01-01T00:00:00+00:00]', $line);
    }

    public function testInvoke(): void
    {
        $services = new ServiceManager();

        $config = ['name' => 'test', 'handlers' => [['name' => TestHandler::class]]];

        $services->setService('EnliteMonologOptions', new MonologOptions($config));

        $sut = new MonologServiceFactory();

        $service = $sut(new ContainerMock($services), 'EnliteMonolog');
        $this->assertInstanceOf(Logger::class, $service);
        $this->assertEquals('test', $service->getName());

        $this->assertInstanceOf(TestHandler::class, $service->popHandler());
    }

    public function testCreateHandlerFromOptions(): void
    {
        $sut = new MonologServiceFactory();

        $services = new ServiceManager();

        $options = new MonologOptions();
        $options->setHandlers([
            'HandlerMock' => [
                'name' => HandlerMock::class,
                'args' => [
                    'path' => '/FooBar',
                ],
            ],
        ]);

        $result = $sut->createHandler($services, $options, [
            'name' => HandlerMock::class,
            'args' => [
                'handler' => 'HandlerMock',
                'path' => '/FizBuz',
            ],
        ]);

        self::assertInstanceOf(HandlerMock::class, $result);
    }

    public function testCreateHandlerWithInvalidArguments(): void
    {
        $sut = new MonologServiceFactory();

        $services = new ServiceManager();

        $options = new MonologOptions();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'Handler(EnliteMonologTest\Service\HandlerMock) has an invalid argument configuration'
        );

        $sut->createHandler($services, $options, [
            'name' => \EnliteMonologTest\Service\HandlerMock::class,
            'args' => [],
        ]);
    }

    public function testCreateFormatterWithMissingArguments(): void
    {
        $sut = new MonologServiceFactory();

        $services = new ServiceManager();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'Formatter(EnliteMonologTest\Service\FormatterMock) has an invalid argument config'
        );

        $sut->createFormatter($services, [
            'name' => FormatterMock::class,
            'args' => [],
        ]);
    }

    public function testCreateFormatterWithInvalidArguments(): void
    {
        $sut = new MonologServiceFactory();

        $services = new ServiceManager();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'Formatter(EnliteMonologTest\Service\FormatterMock) has an invalid argument config'
        );

        $sut->createFormatter($services, [
            'name' => \EnliteMonologTest\Service\FormatterMock::class,
            'args' => [
                'NotEncoder' => new stdClass(),
            ],
        ]);
    }

    public function testCreateFormatterWithPrivateConstructor(): void
    {
        $sut = new MonologServiceFactory();

        $services = new ServiceManager();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'Formatter(EnliteMonologTest\Service\FormatterPrivateConstructorMock) has an invalid'
        );

        $sut->createFormatter($services, [
            'name' => FormatterPrivateConstructorMock::class,
            'args' => [],
        ]);
    }
}
