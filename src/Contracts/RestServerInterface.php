<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Contracts;

use TheWildFields\Tavriia\Dto\RestRouteDto;

/**
 * Contract for classes that register REST API routes with WordPress.
 */
interface RestServerInterface
{
    /**
     * Register a single route definition with WordPress.
     *
     * Implementations must throw a typed exception on failure; WordPress'
     * bool return value and WP_Error side-channel never cross this boundary.
     *
     * @throws \TheWildFields\Tavriia\Exceptions\RestRouteRegistrationException
     *         When WordPress reports that route registration failed.
     */
    public function register(RestRouteDto $route): void;

    /**
     * Register multiple route definitions in a single call.
     *
     * @param iterable<RestRouteDto> $routes
     *
     * @throws \TheWildFields\Tavriia\Exceptions\RestRouteRegistrationException
     */
    public function registerMany(iterable $routes): void;
}
