<?php

namespace EnliteMonologTest\ZF2\IntegrationTest;

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
}
