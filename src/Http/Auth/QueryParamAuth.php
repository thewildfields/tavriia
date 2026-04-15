<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Http\Auth;

use TheWildFields\Tavriia\Http\RequestBuilder;

/**
 * Query parameter authentication strategy.
 *
 * Appends an authentication parameter to the URL query string.
 * Useful for APIs like Google that require an API key in the URL.
 */
final readonly class QueryParamAuth implements AuthStrategyInterface
{
    public function __construct(
        private string $value,
        private string $paramName = 'key',
    ) {}

    public function applyTo(RequestBuilder $builder): RequestBuilder
    {
        return $builder;
    }

    public function applyToUrl(string $url): string
    {
        $separator = str_contains($url, '?') ? '&' : '?';

        return $url . $separator . urlencode($this->paramName) . '=' . urlencode($this->value);
    }
}
