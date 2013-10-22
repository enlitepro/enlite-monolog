Monolog integration to ZF2 [![Build Status](https://travis-ci.org/enlitepro/enlite-monolog.png)](https://travis-ci.org/enlitepro/enlite-monolog)
==============

Integration to Zend Framework 2 grate log system [monolog](https://github.com/Seldaek/monolog)

INSTALL
=======

The recommended way to install is through composer.

```json
{
    "require": {
        "enlitepro/enlite-monolog": "~1.1"
    }
}
```

USAGE
=====

Add `EnliteMonolog` to your `config/application.config.php` to enable module.

```php
// usage over service locator
$serviceLocator->get('EnliteMonologService')->addDebug('hello world');

use EnliteMonolog\Service\MonologServiceAwareInterface,
    EnliteMonolog\Service\MonologServiceAwareTrait;

// usage in your services
class MyService implements MonologServiceAwareInterface
{
    use MonologServiceAwareTrait;

    public function whatever()
    {
        $this->getMonologService()->addDebug('hello world');
    }
}

```

By default it write logs to `data/logs/application.log`. If you want change this behaviour, add your config following:

```php
    'EnliteMonolog' => array(
        'EnliteMonologService' => array(
            // Logger name
            // 'name' => 'EnliteMonolog',

            // Handlers, it can be service locator alias(string) or config(array)
            'handlers' => array(
                // by config
                'default' => array(
                    'name' => 'Monolog\Handler\StreamHandler',
                    'args' => array(
                        'path' => 'data/log/application.log',
                        'level' => \Monolog\Logger::DEBUG,
                        'bubble' => true
                    )
                ),

                // by service locator
                'MyMonologHandler'
            )
        ),

        // you can specify another logger
        // for example ChromePHPHandler

        'MyChromeLogger' => array(
            'name' => 'MyName',
            'handlers' => array(
                array(
                    'name' => 'Monolog\Handler\ChromePHPHandler',
                )
            )
        )
    ),
```

now you can use it

```php
$serviceLocator->get('EnliteMonologService')->addDebug('hello world');
$serviceLocator->get('MyChromeLogger')->addInfo('hello world');
```