<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonologTest\Service;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;

class HandlerMock implements HandlerInterface
{

    protected $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function isHandling(array $record): bool
    {
        return true;
    }

    public function handle(array $record): bool
    {
        return true;
    }

    public function handleBatch(array $records): void
    {
    }

    public function pushProcessor($callback)
    {
    }

    public function popProcessor()
    {
    }

    public function setFormatter(FormatterInterface $formatter)
    {
    }

    public function getFormatter()
    {
    }

    public function close(): void
    {
    }
}
