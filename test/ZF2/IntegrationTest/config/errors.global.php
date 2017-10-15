<?php

return array(
    'EnliteMonologErrorHandlers' => array(
        'EnliteMonologService' => array(
            'logger' => 'ErrorLogger',
        ),
    ),
    'EnliteMonolog' => array(
        'ErrorLogger' => array(
            'name' => 'ErrorLogger',
            'handlers' => array(
                'TestHandler' => array(
                    'name' => 'Monolog\Handler\TestHandler',
                ),
            ),
        ),
    ),
);
