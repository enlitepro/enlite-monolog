<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonolog;

use EnliteMonolog\Service\ErrorHandlerListener;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\InitProviderInterface;
use Zend\ModuleManager\ModuleManagerInterface;

class Module implements
    AutoloaderProviderInterface,
    InitProviderInterface,
    ConfigProviderInterface
{
    /**
     * @param ModuleManagerInterface $moduleManager
     * @return void
     */
    public function init(ModuleManagerInterface $moduleManager)
    {
        $errorHandlerListener = new ErrorHandlerListener();
        $errorHandlerListener->attach($moduleManager->getEventManager());
    }

    /**
     * {@inheritdoc}
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/../../src/' . __NAMESPACE__,
                ),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return include __DIR__ . "/../../config/module.config.php";
    }
}
