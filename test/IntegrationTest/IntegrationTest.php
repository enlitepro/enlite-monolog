<?php

namespace EnliteMonologTest\IntegrationTest;

use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Zend\Mvc\Application;

final class IntegrationTest extends TestCase
{
    /** @var Application */
    private $sut;

    protected function setUp(): void
    {
        $modules = [
            'EnliteMonolog',
        ];
        if (class_exists(\Zend\Router\Module::class)) {
            $modules[] = \Zend\Router\Module::class;
        }

        $this->sut = Application::init([
            'module_listener_options' => [
                'config_glob_paths' => [
                    __DIR__ . '/config/{{,*.}global,{,*.}local}.php',
                ],
            ],
            'modules' => $modules,
        ]);
    }

    public function testEnliteMonologServiceAvailableViaApplicationContainer(): void
    {
        $services = $this->sut->getServiceManager();

        /** @var Logger $logger */
        $logger = $services->get('EnliteMonologService');

        self::assertInstanceOf(Logger::class, $logger);
    }
}
