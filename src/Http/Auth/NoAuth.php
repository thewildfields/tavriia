<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Http\Auth;

use TheWildFields\Tavriia\Http\RequestBuilder;

/**
 * No-op authentication strategy for unauthenticated APIs.
 */
final readonly class NoAuth implements AuthStrategyInterface
{
    public function applyTo(RequestBuilder $builder): RequestBuilder
    {
        return $builder;
    }

    public function applyToUrl(string $url): string
    {
        return $url;
    }
}
