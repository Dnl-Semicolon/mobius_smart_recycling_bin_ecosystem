<?php

namespace App\Logging\Formatters;

use Monolog\Formatter\JsonFormatter;
use Monolog\LogRecord;

class WideEventFormatter extends JsonFormatter
{
    public function __construct()
    {
        parent::__construct(
            batchMode: self::BATCH_MODE_NEWLINES,
            appendNewline: true,
            ignoreEmptyContextAndExtra: true
        );
    }

    /**
     * Format a log record as a single JSON line.
     *
     * For wide events, we emit the context directly (the wide event data)
     * rather than wrapping it in Monolog's standard format.
     */
    public function format(LogRecord $record): string
    {
        return json_encode(
            $record->context,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        )."\n";
    }
}
