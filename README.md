Monolog integration to ZF3 [![Build Status](https://travis-ci.org/enlitepro/enlite-monolog.png)](https://travis-ci.org/enlitepro/enlite-monolog) [![Code Coverage](https://scrutinizer-ci.com/g/enlitepro/enlite-monolog/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/enlitepro/enlite-monolog/?branch=master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/enlitepro/enlite-monolog/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/enlitepro/enlite-monolog/?branch=master)
==============

Integration to Zend Framework 3 with great logging system [monolog 2](https://github.com/Seldaek/monolog).

INSTALL
=======

The recommended way to install is through composer from command line.

```
composer require enlitepro/enlite-monolog
```

Add `EnliteMonolog` to your `config/application.config.php` to enable module.

Copy the config file `config/monolog.global.php.dist` from the module to `config/autoload/monolog.global.php` in your project.

By default logger write logs to `data/logs/application.log` by copied configuration.
If you want change this behaviour, add your config following:

```php
return [
    'EnliteMonolog' => [
        'EnliteMonologService' => [
            // Logger name
            // 'name' => 'EnliteMonolog',

            // Handlers, it can be service locator alias(string) or config(array)
            'handlers' => [
                // by config
                'default' => [
                    'name' => \Monolog\Handler\StreamHandler::class,
                    'args' => [
                        'stream' => 'data/log/application.log',
                        'level' => \Monolog\Logger::DEBUG,
                        'bubble' => true
                    ],
                    'formatter' => [
                        'name' => \Monolog\Formatter\LogstashFormatter::class,
                        'args' => [
                            'applicationName' => 'My Application',
                        ],
                    ],
                ],

                // by service locator
                'MyMonologHandler'
            ],
        ],

        // you can specify another logger
        // for example ChromePHPHandler

        'MyChromeLogger' => [
            'name' => 'MyName',
            'handlers' => [
                [
                    'name' => \Monolog\Handler\ChromePHPHandler::class,
                ],
            ],
        ],
    ],
];
```

now you can use it:

```php
$container->get('EnliteMonologService')->debug('hello world');
$container->get('MyChromeLogger')->info('hello world');
```

To auto inject logger to your classes you need to setup initializer (it's disabled by default, [that's why](https://docs.zendframework.com/zend-servicemanager/configuring-the-service-manager/#best-practices_2).):

```php
return [
    'service_manager' => [
        'initializers' => [
            \EnliteMonolog\Service\MonologServiceInitializer::class,
        ],
    ],
];
```

Now logger is available in your class.

```php
class MyService implements MonologServiceAwareInterface
{
    use MonologServiceAwareTrait;

    public function whatever()
    {
        $this->getMonologService()->debug('hello world');
    }
}
```

## Contributing

This library attempts to comply with [PSR-1][], [PSR-2][], and [PSR-4][]. If
you notice compliance oversights, please send a patch via pull request.

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md
