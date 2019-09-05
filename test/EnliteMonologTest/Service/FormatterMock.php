<?php

namespace EnliteMonologTest\Service;

use Monolog\Formatter\FormatterInterface;
use RuntimeException;

final class FormatterMock implements FormatterInterface
{
    /** @var callable */
    private $encoder;

    public function __construct(callable $encoder)
    {
        if (!is_callable($encoder)) {
            throw new RuntimeException('Encoder must be callable.');
        }

        $this->encoder = $encoder;
    }

    /**
     * Formats a log record.
     *
     * @param array $record A record to format
     * @return mixed The formatted record
     */
    public function format(array $record)
    {
        return call_user_func($this->encoder, $record);
    }

    /**
     * Formats a set of log records.
     *
     * @param array $records A set of records to format
     * @return mixed The formatted set of records
     */
    public function formatBatch(array $records)
    {
        return array_map([$this, 'format'], $records);
    }
}
