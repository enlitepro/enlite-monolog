<?php

namespace EnliteMonologTest\Module;

use EnliteMonolog\Module;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\ModuleManager;
use Zend\ServiceManager\ServiceManager;

/**
 * @covers \EnliteMonolog\Module
 */
class ModuleTest extends \PHPUnit_Framework_TestCase
{
    /** @var Module */
    private $sut;

    protected function setUp()
    {
        parent::setUp();

        $this->sut = new Module();
    }
    
    public function testGetAutoloaderConfig()
    {
        $actual = $this->sut->getAutoloaderConfig();
        
        self::assertInternalType('array', $actual);
    }
    
    public function testAutoloaderConfigIsSerializable()
    {
        self::assertInternalType('array', unserialize(serialize($this->sut->getAutoloaderConfig())));
    }
    
    public function testGetConfig()
    {
        $actual = $this->sut->getConfig();
        
        self::assertInternalType('array', $actual);
    }
    
    public function testConfigIsSerializable()
    {
        self::assertInternalType('array', unserialize(serialize($this->sut->getConfig())));
    }

    /**
     * @runInSeparateProcess \Monolog\ErrorHandler::register has side-effects.
     */
    public function testInitMultipleErrorHandlers()
    {
        if (!class_exists('\Monolog\ErrorHandler')) {
            self::markTestSkipped('monolog\monolog:^1.6 is required.');
        }

        $logger = new Logger(__METHOD__, array(
            $handler = new TestHandler(),
        ));

        $anotherLogger = new Logger(__METHOD__, array(
            $anotherHandler = new TestHandler(),
        ));

        $services = new ServiceManager();
        $services->setService('FooBar', $logger);
        $services->setService('FizBuz', $anotherLogger);
        $services->setService('config', array(
            'EnliteMonologErrorHandlers' => array(
                'FooBar' => array(
                    'logger' => 'FooBar',
                ),
                'FizBuz' => array(
                    'logger' => 'FizBuz',
                ),
            ),
        ));

        $event = new ModuleEvent(ModuleEvent::EVENT_LOAD_MODULES_POST, $this, array(
            'ServiceManager' => $services,
        ));

        $modules = new ModuleManager(array());

        $this->sut->init($modules);

        ErrorHandlerListenerTest::triggerModuleEvent($modules, $event);

        self::assertCount(0, $handler->getRecords());
        self::assertCount(0, $anotherHandler->getRecords());

        try {
            \trigger_error('whoops');
        } catch (\Exception $e) {
        }

        self::assertCount(1, $handler->getRecords());
        self::assertCount(1, $anotherHandler->getRecords());
    }
}
