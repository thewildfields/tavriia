<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Http\Auth;

use TheWildFields\Tavriia\Http\RequestBuilder;

/**
 * HTTP Basic authentication strategy.
 *
 * Encodes username and password as base64 and adds an Authorization header.
 */
final readonly class BasicAuth implements AuthStrategyInterface
{
    public function __construct(
        private string $username,
        private string $password,
    ) {}

    public function applyTo(RequestBuilder $builder): RequestBuilder
    {
        $credentials = base64_encode($this->username . ':' . $this->password);

        return $builder->header('Authorization', 'Basic ' . $credentials);
    }

    public function applyToUrl(string $url): string
    {
        return $url;
    }
}
