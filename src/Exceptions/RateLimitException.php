<?php

declare(strict_types=1);

namespace Nusii\Exceptions;

class RateLimitException extends NusiiException
{
    public function __construct(
        string $message,
        int $code = 429,
        public readonly ?int $retryAfter = null,
        ?array $responseBody = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $responseBody, $previous);
    }
}
