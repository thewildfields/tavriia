<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Http\Auth;

use TheWildFields\Tavriia\Http\RequestBuilder;

/**
 * Bearer token authentication strategy.
 *
 * Adds an Authorization header with a Bearer token.
 */
final readonly class BearerTokenAuth implements AuthStrategyInterface
{
    public function __construct(
        private string $token,
    ) {}

    public function applyTo(RequestBuilder $builder): RequestBuilder
    {
        return $builder->header('Authorization', 'Bearer ' . $this->token);
    }

    public function applyToUrl(string $url): string
    {
        return $url;
    }
}
