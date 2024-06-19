<?php

namespace App\Exceptions;

use Exception;

class SystemUpdateException extends Exception
{
    public function __construct(
        string $message,
        public readonly ?string $stage = null,
        public readonly array $context = [],
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
