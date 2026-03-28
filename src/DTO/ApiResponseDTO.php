<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\DTO;

/**
 * Immutable data transfer object representing an HTTP API response.
 */
final readonly class ApiResponseDTO
{
    /**
     * @param int                  $statusCode  HTTP status code.
     * @param string               $body        Raw response body.
     * @param array<string, string> $headers     Response headers.
     */
    public function __construct(
        public int $statusCode,
        public string $body,
        /** @var array<string, string> */
        public array $headers = [],
    ) {}

    /**
     * Decode the response body as JSON and return the result.
     *
     * @throws \JsonException When the body is not valid JSON.
     */
    public function json(): mixed
    {
        return json_decode($this->body, associative: true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * Whether the response status code indicates success (2xx).
     */
    public function isSuccess(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * Whether the response status code indicates a client error (4xx).
     */
    public function isClientError(): bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    /**
     * Whether the response status code indicates a server error (5xx).
     */
    public function isServerError(): bool
    {
        return $this->statusCode >= 500;
    }
}
