<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonolog;

use Zend\ModuleManager\Feature\ConfigProviderInterface;

final class Module implements ConfigProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfig(): array
    {
        return include __DIR__ . '/../../config/module.config.php';
    }
}
