<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Exceptions;

use TheWildFields\Tavriia\DTO\ApiResponseDTO;

/**
 * Thrown when an HTTP response indicates a failure (4xx / 5xx status codes).
 */
final class ApiResponseException extends \RuntimeException
{
    public function __construct(
        string $message,
        private readonly ApiResponseDTO $response,
        \Throwable $previous = null,
    ) {
        parent::__construct($message, $response->statusCode, $previous);
    }

    public function getResponse(): ApiResponseDTO
    {
        return $this->response;
    }

    public static function forResponse(ApiResponseDTO $response): self
    {
        return new self(
            sprintf(
                'HTTP response error: status %d — %s',
                $response->statusCode,
                $response->body,
            ),
            $response,
        );
    }
}
