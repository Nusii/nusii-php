<?php

declare(strict_types=1);

namespace Nusii\Exceptions;

class NusiiException extends \Exception
{
    public function __construct(
        string $message,
        int $code = 0,
        public readonly ?array $responseBody = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
