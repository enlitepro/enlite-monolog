<?php

namespace EnliteMonologTest\Service;

final class ProcessorMock
{
    private $argument;

    public function __construct($argument)
    {
        $this->argument = $argument;
    }

    public function __invoke(array $record)
    {
        return $record;
    }
}
