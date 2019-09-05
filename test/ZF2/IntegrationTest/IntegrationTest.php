<?php

namespace EnliteMonologTest\ZF2\IntegrationTest;

use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Zend\Mvc\Application;

final class IntegrationTest extends TestCase
{
    /** @var Application */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = Application::init([
            'module_listener_options' => [
                'config_glob_paths' => [
                    __DIR__ . '/config/{{,*.}global,{,*.}local}.php',
                ],
            ],
            'modules' => [
                'EnliteMonolog',
            ],
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
