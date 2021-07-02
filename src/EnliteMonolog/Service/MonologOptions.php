<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonolog\Service;

use Laminas\Stdlib\AbstractOptions;

class MonologOptions extends AbstractOptions
{

    /**
     * Logger name
     *
     * @var string
     */
    protected $name = 'EnliteMonolog';

    /**
     * Handlers
     *
     * @var array
     */
    protected $handlers = [];

    /**
     * @var array
     */
    protected $processors = [];

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setHandlers(array $handlers): void
    {
        $this->handlers = $handlers;
    }

    public function getHandlers(): array
    {
        return $this->handlers;
    }

    public function setProcessors(array $processors = []): void
    {
        $this->processors = $processors;
    }

    public function getProcessors(): array
    {
        return $this->processors;
    }
}
