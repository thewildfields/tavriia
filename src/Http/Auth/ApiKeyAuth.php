<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Http\Auth;

use TheWildFields\Tavriia\Http\RequestBuilder;

/**
 * API key authentication strategy using a custom header.
 *
 * Adds a configurable header containing the API key.
 */
final readonly class ApiKeyAuth implements AuthStrategyInterface
{
    public function __construct(
        private string $key,
        private string $headerName = 'X-API-Key',
    ) {}

    public function applyTo(RequestBuilder $builder): RequestBuilder
    {
        return $builder->header($this->headerName, $this->key);
    }

    public function applyToUrl(string $url): string
    {
        return $url;
    }
}
