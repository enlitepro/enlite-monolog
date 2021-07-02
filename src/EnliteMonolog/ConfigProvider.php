<?php

namespace EnliteMonolog;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    public function getDependencyConfig(): array
    {
        return [
            'abstract_factories' => [
                \EnliteMonolog\Service\MonologServiceAbstractFactory::class,
            ],
            'initializers' => [
                \EnliteMonolog\Service\MonologServiceInitializer::class,
            ],
        ];
    }
}
