<?php

return array(
    'service_manager' => array(
        'abstract_factories' => array(
            'EnliteMonolog\Service\MonologServiceAbstractFactory'
        ),
        'initializers' => array(
            'EnliteMonolog\Service\MonologServiceInitializer'
        )
    )
);
