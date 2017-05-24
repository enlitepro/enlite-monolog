<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonolog\Service;

use Monolog\Logger;

interface MonologServiceAwareInterface
{

    /**
     * @param Logger $monologService
     * @return void
     */
    public function setMonologService(Logger $monologService);

    /**
     * @return Logger
     */
    public function getMonologService();
}
