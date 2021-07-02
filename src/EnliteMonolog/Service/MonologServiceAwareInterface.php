<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonolog\Service;

use Monolog\Logger;

interface MonologServiceAwareInterface
{
    public function setMonologService(Logger $monologService): void;

    public function getMonologService(): ?Logger;
}
