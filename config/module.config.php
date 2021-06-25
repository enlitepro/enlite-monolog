<?php

return [
    'service_manager' => [
        'abstract_factories' => [
            EnliteMonolog\Service\MonologServiceAbstractFactory::class,
        ],
        'initializers' => [
            EnliteMonolog\Service\MonologServiceInitializer::class,
        ],
    ],
];
