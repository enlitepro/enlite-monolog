<?php

namespace EnliteMonologTest\Module;

use EnliteMonolog\Service\ErrorHandlerListener;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Zend\EventManager\EventManagerInterface;
use Zend\ModuleManager\Listener\ConfigListener;
use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\ModuleManager;
use Zend\ServiceManager\ServiceManager;

/**
 * @covers \EnliteMonolog\Service\ErrorHandlerListener
 * @runTestsInSeparateProcesses \Monolog\ErrorHandler::register has side-effects.
 */
class ErrorHandlerListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var TestHandler */
    private $handler;

    /** @var EventManagerInterface */
    private $events;

    /** @var ModuleManager */
    private $modules;

    /** @var ConfigListener */
    private $configListener;

    /** @var ModuleEvent */
    private $event;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Logger */
    private $mockLogger;

    /** @var Logger */
    private $logger;

    /** @var ServiceManager */
    private $services;

    /** @var ErrorHandlerListener */
    private $sut;

    public static function setUpBeforeClass()
    {
        if (!class_exists('\Monolog\ErrorHandler')) {
            self::markTestSkipped('monolog\monolog:^1.6 is required.');
        }
    }

    protected function setUp()
    {
        $this->services = new ServiceManager();

        $this->handler = new TestHandler();

        $this->logger = new Logger(__METHOD__, array(
            $this->handler,
        ));

        $this->mockLogger = $this->getMockBuilder('\Psr\Log\LoggerInterface')
            ->getMock();

        $this->configListener = new ConfigListener();

        $this->event = new ModuleEvent(ModuleEvent::EVENT_LOAD_MODULES_POST, $this, array(
            'ServiceManager' => $this->services,
        ));

        $this->event->setConfigListener($this->configListener);

        $this->modules = new ModuleManager(array());

        $this->events = $this->modules->getEventManager();

        $this->sut = new ErrorHandlerListener();

        $this->sut->attach($this->events);
    }

    public function testRegisterErrorHandlers()
    {
        $this->services->setService('config', array(
            'EnliteMonologErrorHandlers' => array(
                'FooBar' => array(
                    'logger' => 'FooBar',
                ),
                'FizBuz' => array(
                    'logger' => 'FizBuz',
                ),
            ),
        ));

        $this->services->setService('FooBar', $this->logger);

        $this->services->setService('FizBuz', $this->mockLogger);

        $handlers = $this->sut->registerErrorHandlers($this->event);

        self::assertArrayHasKey('FooBar', $handlers);
        self::assertInstanceOf('\Monolog\ErrorHandler', $handlers['FooBar']);

        self::assertArrayHasKey('FizBuz', $handlers);
        self::assertInstanceOf('\Monolog\ErrorHandler', $handlers['FizBuz']);
    }

    public function testRegisterErrorHandlersViaConfigListener()
    {
        $this->configListener->setMergedConfig(array(
            'EnliteMonologErrorHandlers' => array(
                'FooBar' => array(
                    'logger' => 'FooBar',
                ),
                'FizBuz' => array(
                    'logger' => 'FizBuz',
                ),
            ),
        ));

        $this->services->setService('FooBar', $this->logger);

        $this->services->setService('FizBuz', $this->mockLogger);

        $handlers = $this->sut->registerErrorHandlers($this->event);

        self::assertArrayHasKey('FooBar', $handlers);
        self::assertInstanceOf('\Monolog\ErrorHandler', $handlers['FooBar']);

        self::assertArrayHasKey('FizBuz', $handlers);
        self::assertInstanceOf('\Monolog\ErrorHandler', $handlers['FizBuz']);
    }

    public function testNotRegisterErrorHandlersDueToMissingContainer()
    {
        $event = new ModuleEvent();

        $handlers = $this->sut->registerErrorHandlers($event);

        self::assertCount(0, $handlers);
    }

    public function testNotRegisterErrorHandlersDueToInvalidConfig()
    {
        $this->services->setService('config', new \stdClass());

        $handlers = $this->sut->registerErrorHandlers($this->event);

        self::assertCount(0, $handlers);
    }

    public function testNotRegisterErrorHandlersDueToMissingConfigList()
    {
        $this->services->setService('config', array(

        ));

        $event = new ModuleEvent(ModuleEvent::EVENT_LOAD_MODULES_POST, $this, array(
            'ServiceManager' => $this->services,
        ));

        $handlers = $this->sut->registerErrorHandlers($event);

        self::assertCount(0, $handlers);
    }

    public function testNotRegisterErrorHandlersDueToInvalidConfigList()
    {
        $this->services->setService('config', array(
            'EnliteMonologErrorHandlers' => 'FooBar',
        ));

        $handlers = $this->sut->registerErrorHandlers($this->event);

        self::assertCount(0, $handlers);
    }

    public function testNotRegisterErrorHandlersDueToEmptyConfigList()
    {
        $this->services->setService('config', array(
            'EnliteMonologErrorHandlers' => array(

            ),
        ));

        $handlers = $this->sut->registerErrorHandlers($this->event);

        self::assertCount(0, $handlers);
    }

    public function testNotRegisterErrorHandlersDueToMissingLogger()
    {
        $this->services->setService('config', array(
            'EnliteMonologErrorHandlers' => array(
                'FooBar' => array(
                    'logger' => 'FooBar',
                ),
            ),
        ));

        $handlers = $this->sut->registerErrorHandlers($this->event);

        self::assertArrayHasKey('FooBar', $handlers);
        self::assertNull($handlers['FooBar']);
    }

    public function testNotRegisterErrorHandlersDueToInvalidLogger()
    {
        $this->services->setService('config', array(
            'EnliteMonologErrorHandlers' => array(
                'FooBar' => array(
                    'logger' => 'FooBar',
                ),
            ),
        ));

        $this->services->setService('FooBar', new \stdClass());

        $handlers = $this->sut->registerErrorHandlers($this->event);

        self::assertArrayHasKey('FooBar', $handlers);
        self::assertNull($handlers['FooBar']);
    }

    /**
     * Normalizes event-manager trigger functionality, between versions 2 and 3.
     *
     * @param ModuleManager $modules
     * @param ModuleEvent $event
     * @return \Zend\EventManager\ResponseCollection
     */
    public static function triggerModuleEvent(ModuleManager $modules, ModuleEvent $event)
    {
        $events = $modules->getEventManager();

        if (is_callable(array($events, 'setEventPrototype'))) {
            $events->setEventPrototype($event);
        }

        if (is_callable(array($events, 'triggerEvent'))) {
            return $events->triggerEvent($event);
        }

        if (is_callable(array($events, 'setEventPrototype'))) {
            return $events->trigger($event->getName(), $event->getTarget(), $event->getParams());
        } else {
            return $events->trigger($event);
        }
    }

    public function testInitRegistersErrorHandlers()
    {
        $this->services->setService('FooBar', $this->logger);
        $this->services->setService('FizBuz', $this->mockLogger);
        $this->services->setService('config', array(
            'EnliteMonologErrorHandlers' => array(
                'FooBar' => array(
                    'logger' => 'FooBar',
                ),
                'FizBuz' => array(
                    'logger' => 'FizBuz',
                ),
            ),
        ));

        $responses = self::triggerModuleEvent($this->modules, $this->event);

        self::assertCount(1, $responses);
        self::assertInternalType('array', $handlers = $responses[0]);

        \PHPUnit_Framework_TestCase::assertArrayHasKey('FooBar', $handlers);
        \PHPUnit_Framework_TestCase::assertInstanceOf('\Monolog\ErrorHandler', $handlers['FooBar']);

        \PHPUnit_Framework_TestCase::assertArrayHasKey('FizBuz', $handlers);
        \PHPUnit_Framework_TestCase::assertInstanceOf('\Monolog\ErrorHandler', $handlers['FizBuz']);
    }

    public function testRegisterErrorHandlerOnly()
    {
        $this->services->setService('FooBar', $this->logger);
        $this->services->setService('config', array(
            'EnliteMonologErrorHandlers' => array(
                'FooBar' => array(
                    'logger' => 'FooBar',
                    'exceptionLevel' => false,
                    'fatalLevel' => false,
                ),
            ),
        ));

        self::triggerModuleEvent($this->modules, $this->event);

        self::assertCount(0, $this->handler->getRecords());

        try {
            \trigger_error('whoops');
        } catch (\Exception $e) {
        }

        self::assertCount(1, $this->handler->getRecords());
    }

    public function testRegisterMultipleErrorHandlers()
    {
        $anotherHandler = new TestHandler();

        $anotherLogger = new Logger(__METHOD__, array(
            $anotherHandler
        ));

        $this->services->setService('FooBar', $this->logger);
        $this->services->setService('FizBuz', $anotherLogger);
        $this->services->setService('config', array(
            'EnliteMonologErrorHandlers' => array(
                'FooBar' => array(
                    'logger' => 'FooBar',
                ),
                'FizBuz' => array(
                    'logger' => 'FizBuz',
                ),
            ),
        ));

        self::triggerModuleEvent($this->modules, $this->event);

        self::assertCount(0, $this->handler->getRecords());
        self::assertCount(0, $anotherHandler->getRecords());

        try {
            \trigger_error('whoops');
        } catch (\Exception $e) {
        }

        self::assertCount(1, $this->handler->getRecords());
        self::assertCount(1, $anotherHandler->getRecords());
    }

    public function testRegisterExceptionHandlerOnly()
    {
        $this->services->setService('FooBar', $this->logger);
        $this->services->setService('config', array(
            'EnliteMonologErrorHandlers' => array(
                'FooBar' => array(
                    'logger' => 'FooBar',
                    'errorLevelMap' => false,
                    'fatalLevel' => false,
                ),
            ),
        ));

        $responses = self::triggerModuleEvent($this->modules, $this->event);

        self::assertCount(1, $responses);
        self::assertInternalType('array', $handlers = $responses[0]);

        \PHPUnit_Framework_TestCase::assertArrayHasKey('FooBar', $handlers);
        \PHPUnit_Framework_TestCase::assertInstanceOf('\Monolog\ErrorHandler', $handlers['FooBar']);
    }

    public function testRegisterFatalHandlerOnly()
    {
        $this->services->setService('FooBar', $this->logger);
        $this->services->setService('config', array(
            'EnliteMonologErrorHandlers' => array(
                'FooBar' => array(
                    'logger' => 'FooBar',
                    'errorLevelMap' => false,
                    'exceptionLevel' => false,
                ),
            ),
        ));

        $responses = self::triggerModuleEvent($this->modules, $this->event);

        self::assertCount(1, $responses);
        self::assertInternalType('array', $handlers = $responses[0]);

        \PHPUnit_Framework_TestCase::assertArrayHasKey('FooBar', $handlers);
        \PHPUnit_Framework_TestCase::assertInstanceOf('\Monolog\ErrorHandler', $handlers['FooBar']);
    }
}
