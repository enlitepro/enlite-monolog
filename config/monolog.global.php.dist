<?php

use Monolog\Handler\StreamHandler;

return [
    'service_manager' => [
        'initializers' => [
            // You can enable initializer if you want
            \EnliteMonolog\Service\MonologServiceInitializer::class,
        ],
    ],
    'EnliteMonolog' => [
        // Logger name
        'EnliteMonologService' => [
            // name of
            'name' => 'default',
            // Handlers, it can be service manager alias(string) or config(array)
            'handlers' => [
                'default' => [
                    'name' => StreamHandler::class,
                    'args' => [
                        'stream' => 'data/log/application.log',
                        'level' => \Monolog\Logger::DEBUG,
                        'bubble' => true,
                    ],
                ],
            ],
        ],
    ],
];
