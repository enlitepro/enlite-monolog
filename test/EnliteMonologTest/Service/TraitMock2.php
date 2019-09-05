<?php

namespace EnliteMonologTest\Service;

use EnliteMonolog\Service\MonologServiceAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

final class TraitMock2
{
    use MonologServiceAwareTrait;
    use ServiceLocatorAwareTrait;
}
