<?php

namespace EnliteMonologTest\Service;

use EnliteMonolog\Service\MonologServiceAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class TraitMock2
{
    use MonologServiceAwareTrait;
    use ServiceLocatorAwareTrait;
}
