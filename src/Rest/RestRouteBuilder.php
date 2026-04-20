<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Rest;

use TheWildFields\Tavriia\Dto\RestRouteDto;
use TheWildFields\Tavriia\Exceptions\RestRouteRegistrationException;

/**
 * Fluent, immutable builder for RestRouteDto instances.
 *
 * Each configuration method returns a new instance so builders can be
 * reused and composed safely. Call build() to produce the immutable DTO
 * that RestServer::register() consumes.
 *
 * Usage:
 *   $route = (new RestRouteBuilder('my-plugin/v1', '/events/(?P<id>\d+)'))
 *       ->get([$controller, 'show'])
 *       ->permissionCallback('__return_true')
 *       ->arg('id', ['type' => 'integer', 'required' => true])
 *       ->build();
 */
final class RestRouteBuilder
{
    private string $methods = RestRouteDto::METHOD_GET;

    /** @var callable|null */
    private $callback = null;

    /** @var callable|null */
    private $permissionCallback = null;

    /** @var array<string, array<string, mixed>> */
    private array $args = [];

    /** @var callable|null */
    private $schema = null;

    private bool $override = false;

    public function __construct(
        private readonly string $namespace,
        private readonly string $route,
    ) {}

    /**
     * Set the HTTP method(s) this route responds to.
     *
     * Pass a single method string ("GET") or a comma-separated list
     * ("GET, POST") mirroring what WordPress accepts.
     */
    public function methods(string $methods): self
    {
        $clone = clone $this;
        $clone->methods = $methods;

        return $clone;
    }

    /**
     * Shortcut: bind this route to GET with the given callback.
     */
    public function get(callable $callback): self
    {
        return $this->methods(RestRouteDto::METHOD_GET)->callback($callback);
    }

    /**
     * Shortcut: bind this route to POST with the given callback.
     */
    public function post(callable $callback): self
    {
        return $this->methods(RestRouteDto::METHOD_POST)->callback($callback);
    }

    /**
     * Shortcut: bind this route to PUT with the given callback.
     */
    public function put(callable $callback): self
    {
        return $this->methods(RestRouteDto::METHOD_PUT)->callback($callback);
    }

    /**
     * Shortcut: bind this route to PATCH with the given callback.
     */
    public function patch(callable $callback): self
    {
        return $this->methods(RestRouteDto::METHOD_PATCH)->callback($callback);
    }

    /**
     * Shortcut: bind this route to DELETE with the given callback.
     */
    public function delete(callable $callback): self
    {
        return $this->methods(RestRouteDto::METHOD_DELETE)->callback($callback);
    }

    /**
     * Set the request-handling callback directly.
     */
    public function callback(callable $callback): self
    {
        $clone = clone $this;
        $clone->callback = $callback;

        return $clone;
    }

    /**
     * Set the permission callback used to authorize the request.
     *
     * WordPress emits a doing_it_wrong() notice for routes without an
     * explicit permission callback, so this is effectively required.
     */
    public function permissionCallback(callable $callback): self
    {
        $clone = clone $this;
        $clone->permissionCallback = $callback;

        return $clone;
    }

    /**
     * Mark this route as publicly accessible (permission_callback => __return_true).
     */
    public function public(): self
    {
        return $this->permissionCallback('__return_true');
    }

    /**
     * Declare a single argument accepted by this route.
     *
     * @param array<string, mixed> $definition WP argument schema entry
     *        (e.g. ['type' => 'integer', 'required' => true]).
     */
    public function arg(string $name, array $definition): self
    {
        $clone = clone $this;
        $clone->args[$name] = $definition;

        return $clone;
    }

    /**
     * Replace the entire argument schema for this route.
     *
     * @param array<string, array<string, mixed>> $args
     */
    public function args(array $args): self
    {
        $clone = clone $this;
        $clone->args = $args;

        return $clone;
    }

    /**
     * Attach a schema callback to this route.
     */
    public function schema(callable $schema): self
    {
        $clone = clone $this;
        $clone->schema = $schema;

        return $clone;
    }

    /**
     * Toggle the "override existing registration" flag.
     */
    public function override(bool $override = true): self
    {
        $clone = clone $this;
        $clone->override = $override;

        return $clone;
    }

    /**
     * Build and return the immutable RestRouteDto.
     *
     * @throws RestRouteRegistrationException When required fields are missing.
     */
    public function build(): RestRouteDto
    {
        if ($this->namespace === '') {
            throw RestRouteRegistrationException::forMissingNamespace();
        }

        if ($this->route === '') {
            throw RestRouteRegistrationException::forMissingRoute();
        }

        if ($this->callback === null) {
            throw new RestRouteRegistrationException(
                sprintf('REST route "%s" (%s) is missing a request callback.', $this->route, $this->namespace),
            );
        }

        if ($this->permissionCallback === null) {
            throw new RestRouteRegistrationException(
                sprintf('REST route "%s" (%s) is missing a permission callback.', $this->route, $this->namespace),
            );
        }

        return new RestRouteDto(
            namespace: $this->namespace,
            route: $this->route,
            methods: $this->methods,
            callback: $this->callback,
            permissionCallback: $this->permissionCallback,
            args: $this->args,
            schema: $this->schema,
            override: $this->override,
        );
    }
}
