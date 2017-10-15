<?php

namespace EnliteMonologTest\ZF2\IntegrationTest;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Zend\Mvc\Application;

class IntegrationTest extends TestCase
{
    /** @var Application */
    private $sut;

    protected function setUp()
    {
        $this->sut = Application::init(array(
            'module_listener_options' => array(
                'config_glob_paths' => array(
                    __DIR__ . '/config/{{,*.}global,{,*.}local}.php',
                ),
            ),
            'modules' => array(
                'EnliteMonolog',
            ),
        ));
    }

    public function testEnliteMonologServiceAvailableViaApplicationContainer()
    {
        $services = $this->sut->getServiceManager();

        /** @var Logger $logger */
        $logger = $services->get('EnliteMonologService');

        self::assertInstanceOf('\Monolog\Logger', $logger);
    }

    /**
     * @runInSeparateProcess \Monolog\ErrorHandler::register has side-effects.
     */
    public function testRegistersErrorHandler()
    {
        if (!class_exists('\Monolog\ErrorHandler')) {
            self::markTestSkipped('monolog\monolog:^1.6 is required.');
        }

        $application = Application::init(array(
            'module_listener_options' => array(
                'config_glob_paths' => array(
                    __DIR__ . '/config/{{,*.}global,{,*.}local}.php',
                ),
            ),
            'modules' => array(
                'EnliteMonolog',
            ),
        ));

        $services = $application->getServiceManager();

        /** @var Logger $logger */
        $logger = $services->get('ErrorLogger');

        self::assertInstanceOf('\Monolog\Logger', $logger);
        self::assertCount(1, $handlers = $logger->getHandlers());

        /** @var TestHandler $handler */
        $handler = $handlers[0];
        self::assertInstanceOf('\Monolog\Handler\TestHandler', $handler);

        self::assertCount(0, $handler->getRecords());

        try {
            \trigger_error('whoops');
        } catch (\Exception $e) {
        }

        self::assertCount(1, $handler->getRecords());
    }
}
