<?php

namespace App\Exceptions;

use RuntimeException;

class WayoutApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $status = 502,
        public readonly ?string $apiCode = null,
        public readonly array $response = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $status, $previous);
    }
}
