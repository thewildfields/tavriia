<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Dto;

/**
 * Immutable data transfer object describing a single WordPress REST API route.
 *
 * A RestRouteDto is the typed equivalent of the array passed as the
 * third argument to WordPress' register_rest_route() function, plus the
 * first two positional arguments (namespace and route pattern).
 *
 * Plugin code should rarely construct a RestRouteDto directly — prefer
 * RestRouteBuilder, which offers a fluent, validated API.
 */
final readonly class RestRouteDto
{
    public const METHOD_GET     = 'GET';
    public const METHOD_POST    = 'POST';
    public const METHOD_PUT     = 'PUT';
    public const METHOD_PATCH   = 'PATCH';
    public const METHOD_DELETE  = 'DELETE';
    public const METHODS_ALL    = 'GET, POST, PUT, PATCH, DELETE';

    /**
     * @param string                         $namespace           The REST namespace (e.g. "my-plugin/v1").
     * @param string                         $route               The route pattern (e.g. "/events/(?P<id>\d+)").
     * @param string                         $methods             HTTP methods, comma-separated (e.g. "GET" or "GET, POST").
     * @param callable                       $callback            Callable that handles a matching request.
     * @param callable                       $permissionCallback  Callable that authorizes the request.
     * @param array<string, array<string, mixed>> $args           Argument schema for parameter validation.
     * @param callable|null                  $schema              Optional schema callback for the route.
     * @param bool                           $override            Whether to replace an existing registration.
     */
    public function __construct(
        public string $namespace,
        public string $route,
        public string $methods,
        public mixed $callback,
        public mixed $permissionCallback,
        public array $args = [],
        public mixed $schema = null,
        public bool $override = false,
    ) {}

    /**
     * Build a WordPress-compatible endpoint definition array.
     *
     * This is the value passed as the third argument to register_rest_route().
     *
     * @return array<string, mixed>
     */
    public function toWpArgs(): array
    {
        $args = [
            'methods'             => $this->methods,
            'callback'            => $this->callback,
            'permission_callback' => $this->permissionCallback,
            'args'                => $this->args,
        ];

        if ($this->schema !== null) {
            $args['schema'] = $this->schema;
        }

        return $args;
    }
}
