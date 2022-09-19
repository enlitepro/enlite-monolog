<?php
// @codingStandardsIgnoreFile

/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace EnliteMonologTest\Service;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\LogRecord;

if (interface_exists(HandlerInterface::class)) {
    if (\Monolog\Logger::API === 2) {
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
    } else {
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

            public function isHandling(LogRecord $record): bool
            {
                return true;
            }

            public function handle(LogRecord $record): bool
            {
                return true;
            }

            public function handleBatch(LogRecord $records): void
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
    }
} else {
    class HandlerMock
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
}
