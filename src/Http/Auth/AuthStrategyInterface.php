<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Http\Auth;

use TheWildFields\Tavriia\Http\RequestBuilder;

/**
 * Contract for authentication strategies applied to HTTP requests.
 *
 * Implementations can modify either request headers (via RequestBuilder)
 * or the URL (for query parameter-based authentication).
 */
interface AuthStrategyInterface
{
    /**
     * Apply authentication to the request headers.
     */
    public function applyTo(RequestBuilder $builder): RequestBuilder;

    /**
     * Apply authentication to the URL (for query parameter auth).
     */
    public function applyToUrl(string $url): string;
}
