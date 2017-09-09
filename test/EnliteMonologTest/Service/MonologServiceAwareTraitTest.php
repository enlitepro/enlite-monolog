<?php

namespace EnliteMonologTest\Service;

use EnliteMonolog\Service\MonologServiceAwareTrait;
use Monolog\Logger;

/**
 * @requires PHP 5.4
 */
class MonologServiceAwareTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testSetMonologService()
    {
        /** @var MonologServiceAwareTrait $trait */
        $trait = $this->getMockForTrait('\EnliteMonolog\Service\MonologServiceAwareTrait');

        $logger = new Logger(__METHOD__);

        $trait->setMonologService($logger);

        self::assertSame($logger, $trait->getMonologService());
    }
}
