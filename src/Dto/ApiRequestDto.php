<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\DTO;

/**
 * Immutable data transfer object describing an outbound HTTP API request.
 */
final readonly class ApiRequestDTO
{
    /**
     * @param string                        $url        The full URL to request.
     * @param string                        $method     HTTP method (GET, POST, PUT, PATCH, DELETE, etc.).
     * @param array<string, string>         $headers    Map of request headers.
     * @param array<string, mixed>|mixed    $body       Request body — associative array or raw string.
     * @param int                           $timeout    Request timeout in seconds.
     * @param bool                          $sslVerify  Whether to verify SSL certificates.
     */
    public function __construct(
        public string $url,
        public string $method = 'GET',
        public array $headers = [],
        public array|string $body = [],
        public int $timeout = 15,
        public bool $sslVerify = true,
        public array $extraArgs = [],
    ) {}

    /**
     * Build a WordPress-compatible args array for wp_remote_request().
     *
     * @return array<string, mixed>
     */
    public function toWpArgs(): array
    {
        return array_merge(
            $this->extraArgs,
            [
                'method'    => $this->method,
                'headers'   => $this->headers,
                'body'      => $this->body,
                'timeout'   => $this->timeout,
                'sslverify' => $this->sslVerify,
            ],
        );
    }
}
