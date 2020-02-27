<?php

namespace EnliteMonologTest\Service;

use EnliteMonolog\Service\MonologServiceAwareTrait;
use Laminas\ServiceManager\ServiceLocatorAwareTrait;

class TraitMock2
{
    use MonologServiceAwareTrait;
    use ServiceLocatorAwareTrait;
}
