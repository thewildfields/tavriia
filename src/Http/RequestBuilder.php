<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Http;

use TheWildFields\Tavriia\DTO\ApiRequestDTO;

/**
 * Fluent builder for constructing ApiRequestDTO instances.
 *
 * Usage:
 *   $request = (new RequestBuilder('https://api.example.com/endpoint'))
 *       ->method('POST')
 *       ->header('Authorization', 'Bearer token')
 *       ->body(['key' => 'value'])
 *       ->timeout(30)
 *       ->build();
 */
final class RequestBuilder
{
    private string $method = 'GET';
    /** @var array<string, string> */
    private array $headers = [];
    private array|string $body = [];
    private int $timeout = 15;
    private bool $sslVerify = true;
    /** @var array<string, mixed> */
    private array $extraArgs = [];

    public function __construct(private readonly string $url)
    {
    }

    /**
     * Set the HTTP method.
     */
    public function method(string $method): self
    {
        $clone = clone $this;
        $clone->method = strtoupper($method);

        return $clone;
    }

    /**
     * Add a single request header.
     */
    public function header(string $name, string $value): self
    {
        $clone = clone $this;
        $clone->headers[$name] = $value;

        return $clone;
    }

    /**
     * Set all request headers at once.
     *
     * @param array<string, string> $headers
     */
    public function headers(array $headers): self
    {
        $clone = clone $this;
        $clone->headers = $headers;

        return $clone;
    }

    /**
     * Set the request body.
     */
    public function body(array|string $body): self
    {
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }

    /**
     * Set the request body as JSON, automatically setting Content-Type.
     *
     * @throws \JsonException When the body cannot be encoded.
     */
    public function jsonBody(array $data): self
    {
        $clone = clone $this;
        $clone->body = json_encode($data, JSON_THROW_ON_ERROR);
        $clone->headers['Content-Type'] = 'application/json';

        return $clone;
    }

    /**
     * Set the request timeout in seconds.
     */
    public function timeout(int $seconds): self
    {
        $clone = clone $this;
        $clone->timeout = $seconds;

        return $clone;
    }

    /**
     * Toggle SSL certificate verification.
     */
    public function sslVerify(bool $verify): self
    {
        $clone = clone $this;
        $clone->sslVerify = $verify;

        return $clone;
    }

    /**
     * Merge extra WP HTTP API args not covered by named properties.
     *
     * @param array<string, mixed> $args
     */
    public function withArgs(array $args): self
    {
        $clone = clone $this;
        $clone->extraArgs = array_merge($clone->extraArgs, $args);

        return $clone;
    }

    /**
     * Build and return the immutable ApiRequestDTO.
     */
    public function build(): ApiRequestDTO
    {
        return new ApiRequestDTO(
            url: $this->url,
            method: $this->method,
            headers: $this->headers,
            body: $this->body,
            timeout: $this->timeout,
            sslVerify: $this->sslVerify,
            extraArgs: $this->extraArgs,
        );
    }
}
