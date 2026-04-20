<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Exceptions;

use RuntimeException;

/**
 * Thrown when registering a REST route with WordPress fails.
 *
 * register_rest_route() returns false when the namespace or route pattern
 * is invalid. The framework converts that failure into this typed exception
 * so plugin code never has to inspect boolean return values.
 */
final class RestRouteRegistrationException extends RuntimeException
{
    public static function forRoute(string $namespace, string $route): self
    {
        return new self(sprintf(
            'Failed to register REST route "%s" under namespace "%s".',
            $route,
            $namespace,
        ));
    }

    public static function forMissingNamespace(): self
    {
        return new self('REST route namespace must not be empty.');
    }

    public static function forMissingRoute(): self
    {
        return new self('REST route pattern must not be empty.');
    }
}
