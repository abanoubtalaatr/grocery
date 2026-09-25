<?php

namespace App\Exceptions;

use RuntimeException;

class CartOperationException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly int $statusCode = 400
    ) {
        parent::__construct($message);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }
}