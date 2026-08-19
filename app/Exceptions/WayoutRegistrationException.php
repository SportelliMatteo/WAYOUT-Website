<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class WayoutRegistrationException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?int $status = null,
        public readonly ?string $codeName = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
