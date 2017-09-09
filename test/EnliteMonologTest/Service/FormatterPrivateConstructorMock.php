<?php

namespace EnliteMonologTest\Service;

use Monolog\Formatter\FormatterInterface;

final class FormatterPrivateConstructorMock implements FormatterInterface
{
    /**
     * FormatterNamedFactoryMock constructor.
     */
    private function __construct()
    {
    }

    public static function create()
    {
        return new self;
    }


    /**
     * Formats a log record.
     *
     * @param  array $record A record to format
     * @return mixed The formatted record
     */
    public function format(array $record)
    {
        return json_encode($record);
    }

    /**
     * Formats a set of log records.
     *
     * @param  array $records A set of records to format
     * @return mixed The formatted set of records
     */
    public function formatBatch(array $records)
    {
        return array_map(array($this, 'format'), $records);
    }
}
