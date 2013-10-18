<?php

return array(
    'EnliteMonolog' => array(
        // Logger name
        // 'name' => 'EnliteMonolog',

        // Handlers, it can be service manager alias(string) or config(array)
        'handlers' => array(
            'default' => array(
                'name' => 'Monolog\Handler\StreamHandler',
                'args' => array(
                    'path' => 'data/log/application.log',
                    'level' => \Monolog\Logger::DEBUG,
                    'bubble' => true
                )
            )
        )
    ),
    'service_manager' => array(
        'factories' => array(
            'EnliteMonologService' => 'EnliteMonolog\Service\MonologServiceFactory',
            'EnliteMonologOptions' => 'EnliteMonolog\Service\MonologOptionsFactory',
        ),
        'initializers' => array(
            'EnliteMonolog\Service\MonologServiceInitializer'
        )
    )
);