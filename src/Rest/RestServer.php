<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Rest;

use TheWildFields\Tavriia\Contracts\RestServerInterface;
use TheWildFields\Tavriia\Dto\RestRouteDto;
use TheWildFields\Tavriia\Exceptions\RestRouteRegistrationException;

/**
 * Typed wrapper around WordPress' register_rest_route().
 *
 * RestServer is the single entry point for wiring REST endpoints into
 * WordPress. Plugin code constructs RestRouteDto instances (usually via
 * RestRouteBuilder) and hands them to this class inside a rest_api_init
 * action callback.
 *
 * WordPress' register_rest_route() returns a boolean; any false return
 * is converted into a RestRouteRegistrationException so error handling
 * stays on the exception-driven path that the rest of the framework uses.
 *
 * Usage:
 *   add_action('rest_api_init', function () use ($server) {
 *       $route = (new RestRouteBuilder('my-plugin/v1', '/events'))
 *           ->get([$this, 'listEvents'])
 *           ->permissionCallback('__return_true')
 *           ->build();
 *
 *       $server->register($route);
 *   });
 */
final class RestServer implements RestServerInterface
{
    /**
     * Register a single route with WordPress.
     *
     * Must be called inside a rest_api_init action callback — calling it
     * earlier (e.g. during plugin bootstrap) will silently no-op because
     * WordPress has not yet initialized its REST infrastructure.
     *
     * @throws RestRouteRegistrationException When the namespace or route is
     *         empty, or when WordPress reports a failure.
     */
    public function register(RestRouteDto $route): void
    {
        if ($route->namespace === '') {
            throw RestRouteRegistrationException::forMissingNamespace();
        }

        if ($route->route === '') {
            throw RestRouteRegistrationException::forMissingRoute();
        }

        $result = register_rest_route(
            route_namespace: $route->namespace,
            route: $route->route,
            args: $route->toWpArgs(),
            override: $route->override,
        );

        if ($result === false) {
            throw RestRouteRegistrationException::forRoute($route->namespace, $route->route);
        }
    }

    /**
     * Register multiple routes in sequence.
     *
     * If any single registration fails, the exception propagates immediately
     * and any preceding routes in the iterable remain registered.
     *
     * @param iterable<RestRouteDto> $routes
     *
     * @throws RestRouteRegistrationException
     */
    public function registerMany(iterable $routes): void
    {
        foreach ($routes as $route) {
            $this->register($route);
        }
    }
}
