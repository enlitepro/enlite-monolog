<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonologTest\Service;

use EnliteMonolog\Service\MonologServiceAwareInterface;
use Monolog\Logger;

final class ServiceMock implements MonologServiceAwareInterface
{
    protected $service;

    public function setMonologService(Logger $monologService): void
    {
        $this->service = $monologService;
    }

    public function getMonologService(): ?Logger
    {
        return $this->service;
    }
}
