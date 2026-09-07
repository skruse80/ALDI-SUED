<?php

namespace App\Exceptions;

use RuntimeException;

final class TraceIdConflictException extends RuntimeException
{
    public function __construct(string $traceId)
    {
        parent::__construct("Trace ID [{$traceId}] was already used with a different payload.");
    }
}
